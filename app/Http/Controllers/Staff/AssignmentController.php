<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssignmentController extends Controller
{
    /**
     * List all assignments for the staff member
     * Web + API
     */
    public function index(Request $request)
    {
        $staff = Auth::user();
        
        $query = $staff->assignments()
            ->with(['booking.hoarding', 'booking.customer', 'booking.vendor']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                  ->orWhere('title', 'like', '%' . $request->search . '%')
                  ->orWhereHas('booking', function($q) use ($request) {
                      $q->where('id', 'like', '%' . $request->search . '%');
                  });
            });
        }
        
        $assignments = $query->latest()->paginate(20);
        
        // API Response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $assignments
            ]);
        }
        
        // Web Response
        return view('staff.assignments.index', compact('assignments'));
    }
    
    /**
     * Show assignment details
     * Web + API
     */
    public function show(Request $request, $id)
    {
        $staff = Auth::user();
        
        $assignment = $staff->assignments()
            ->with([
                'booking.hoarding',
                'booking.customer',
                'booking.vendor',
                'deliverables',
                'activities.user'
            ])
            ->findOrFail($id);
        
        // API Response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $assignment
            ]);
        }
        
        // Web Response
        return view('staff.assignments.show', compact('assignment'));
    }
    
    /**
     * Accept assignment
     * Web + API
     */
    public function accept(Request $request, $id)
    {
        $staff = Auth::user();
        
        $assignment = $staff->assignments()->findOrFail($id);
        
        if ($assignment->status !== 'pending') {
            return $this->errorResponse('Only pending assignments can be accepted', 400, $request);
        }
        
        $assignment->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'accepted_at' => now(),
        ]);
        
        // Log activity
        $assignment->activities()->create([
            'user_id' => $staff->id,
            'action' => 'accepted',
            'description' => 'Assignment accepted by ' . $staff->name,
        ]);
        
        // Send notification to vendor
        // $assignment->booking->vendor->notify(new AssignmentAccepted($assignment));
        
        $message = 'Assignment accepted successfully!';
        
        // API Response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $assignment->fresh()
            ]);
        }
        
        // Web Response
        return redirect()->route('staff.assignments.show', $id)
            ->with('success', $message);
    }
    
    /**
     * Upload proof/deliverables
     * Web + API
     */
    public function uploadProof(Request $request, $id)
    {
        $staff = Auth::user();
        
        $assignment = $staff->assignments()->findOrFail($id);
        
        if (!in_array($assignment->status, ['pending', 'in_progress'])) {
            return $this->errorResponse('Cannot upload files for this assignment status', 400, $request);
        }
        
        $validated = $request->validate([
            'type' => 'required|in:graphics,printing,mounting,survey',
            'notes' => 'nullable|string',
        ]);
        
        // Type-specific validation and processing
        switch ($validated['type']) {
            case 'graphics':
                return $this->handleGraphicsUpload($request, $assignment);
            case 'printing':
                return $this->handlePrintingUpload($request, $assignment);
            case 'mounting':
                return $this->handleMountingUpload($request, $assignment);
            case 'survey':
                return $this->handleSurveyUpload($request, $assignment);
        }
    }
    
    /**
     * Handle Graphics Designer uploads
     */
    protected function handleGraphicsUpload(Request $request, $assignment)
    {
        $request->validate([
            'design_files' => 'required|array',
            'design_files.*' => 'file|mimes:ai,psd,pdf,png,jpg,jpeg|max:51200',
            'description' => 'nullable|string',
            'dimensions' => 'nullable|string',
            'primary_format' => 'nullable|string',
        ]);
        
        $uploadedFiles = [];
        
        foreach ($request->file('design_files') as $file) {
            $path = $file->store('assignments/graphics/' . $assignment->id, 'public');
            
            $deliverable = $assignment->deliverables()->create([
                'type' => 'graphics_design',
                'file_path' => $path,
                'file_url' => Storage::url($path),
                'filename' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'description' => $request->description,
                'metadata' => json_encode([
                    'dimensions' => $request->dimensions,
                    'primary_format' => $request->primary_format,
                ]),
            ]);
            
            $uploadedFiles[] = $deliverable;
        }
        
        // Update assignment progress
        $assignment->update([
            'status' => 'in_progress',
            'progress' => 75,
        ]);
        
        // Log activity
        $assignment->activities()->create([
            'user_id' => Auth::id(),
            'action' => 'uploaded_design',
            'description' => 'Design files uploaded by ' . Auth::user()->name,
        ]);
        
        // Notify vendor and customer if requested
        if ($request->notify_customer) {
            // $assignment->booking->customer->notify(new DesignUploaded($assignment));
        }
        
        return $this->successResponse('Design files uploaded successfully!', $uploadedFiles, $request);
    }
    
    /**
     * Handle Printer uploads
     */
    protected function handlePrintingUpload(Request $request, $assignment)
    {
        $request->validate([
            'printing_photos' => 'required|array',
            'printing_photos.*' => 'image|max:10240',
            'quality_checks' => 'nullable|array',
            'material_type' => 'nullable|string',
            'print_method' => 'nullable|string',
        ]);
        
        $uploadedFiles = [];
        
        foreach ($request->file('printing_photos') as $file) {
            $path = $file->store('assignments/printing/' . $assignment->id, 'public');
            
            $deliverable = $assignment->deliverables()->create([
                'type' => 'printing_proof',
                'file_path' => $path,
                'file_url' => Storage::url($path),
                'filename' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'metadata' => json_encode([
                    'quality_checks' => $request->quality_checks ?? [],
                    'material_type' => $request->material_type,
                    'print_method' => $request->print_method,
                ]),
            ]);
            
            $uploadedFiles[] = $deliverable;
        }
        
        $assignment->update([
            'status' => 'in_progress',
            'progress' => 80,
        ]);
        
        $assignment->activities()->create([
            'user_id' => Auth::id(),
            'action' => 'uploaded_printing_proof',
            'description' => 'Printing proof uploaded by ' . Auth::user()->name,
        ]);
        
        return $this->successResponse('Printing proof uploaded successfully!', $uploadedFiles, $request);
    }
    
    /**
     * Handle Mounter uploads (POD)
     * This triggers campaign start
     */
    protected function handleMountingUpload(Request $request, $assignment)
    {
        $request->validate([
            'pod_photos' => 'required|array|min:4',
            'pod_photos.*' => 'image|max:10240',
            'pod_video' => 'nullable|file|mimes:mp4,mov,avi|max:51200',
            'checklist' => 'required|array|min:4',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'installation_datetime' => 'required|date',
            'weather' => 'nullable|string',
        ]);
        
        $uploadedFiles = [];
        
        // Upload photos
        foreach ($request->file('pod_photos') as $file) {
            $path = $file->store('assignments/mounting/' . $assignment->id, 'public');
            
            $deliverable = $assignment->deliverables()->create([
                'type' => 'pod_photo',
                'file_path' => $path,
                'file_url' => Storage::url($path),
                'filename' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
            
            $uploadedFiles[] = $deliverable;
        }
        
        // Upload video if provided
        if ($request->hasFile('pod_video')) {
            $path = $request->file('pod_video')->store('assignments/mounting/' . $assignment->id, 'public');
            
            $deliverable = $assignment->deliverables()->create([
                'type' => 'pod_video',
                'file_path' => $path,
                'file_url' => Storage::url($path),
                'filename' => $request->file('pod_video')->getClientOriginalName(),
                'file_type' => $request->file('pod_video')->getMimeType(),
                'file_size' => $request->file('pod_video')->getSize(),
            ]);
            
            $uploadedFiles[] = $deliverable;
        }
        
        // Update assignment
        $assignment->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
            'metadata' => json_encode([
                'checklist' => $request->checklist,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'installation_datetime' => $request->installation_datetime,
                'weather' => $request->weather,
                'team_members' => $request->team_members,
            ]),
        ]);
        
        // IMPORTANT: Start the campaign
        if ($assignment->booking) {
            $assignment->booking->update([
                'status' => 'active',
                'campaign_started_at' => now(),
                'pod_uploaded' => true,
            ]);
            
            // Notify vendor and customer
            // $assignment->booking->vendor->notify(new CampaignStarted($assignment->booking));
            // $assignment->booking->customer->notify(new CampaignStarted($assignment->booking));
        }
        
        $assignment->activities()->create([
            'user_id' => Auth::id(),
            'action' => 'uploaded_pod',
            'description' => 'POD uploaded and campaign started by ' . Auth::user()->name,
        ]);
        
        return $this->successResponse('POD uploaded successfully! Campaign has been started.', $uploadedFiles, $request);
    }
    
    /**
     * Handle Surveyor uploads
     */
    protected function handleSurveyUpload(Request $request, $assignment)
    {
        $request->validate([
            'site_photos' => 'required|array|min:5',
            'site_photos.*' => 'image|max:10240',
            'survey_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'visibility_rating' => 'required|string',
            'traffic_density' => 'required|string',
            'site_condition' => 'required|string',
            'observations' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);
        
        $uploadedFiles = [];
        
        // Upload site photos
        foreach ($request->file('site_photos') as $file) {
            $path = $file->store('assignments/survey/' . $assignment->id, 'public');
            
            $deliverable = $assignment->deliverables()->create([
                'type' => 'survey_photo',
                'file_path' => $path,
                'file_url' => Storage::url($path),
                'filename' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
            
            $uploadedFiles[] = $deliverable;
        }
        
        // Upload survey document if provided
        if ($request->hasFile('survey_document')) {
            $path = $request->file('survey_document')->store('assignments/survey/' . $assignment->id, 'public');
            
            $deliverable = $assignment->deliverables()->create([
                'type' => 'survey_document',
                'file_path' => $path,
                'file_url' => Storage::url($path),
                'filename' => $request->file('survey_document')->getClientOriginalName(),
                'file_type' => $request->file('survey_document')->getMimeType(),
                'file_size' => $request->file('survey_document')->getSize(),
            ]);
            
            $uploadedFiles[] = $deliverable;
        }
        
        // Update assignment with survey data
        $assignment->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
            'metadata' => json_encode([
                'visibility_rating' => $request->visibility_rating,
                'traffic_density' => $request->traffic_density,
                'site_condition' => $request->site_condition,
                'width' => $request->width,
                'height' => $request->height,
                'landmarks' => $request->landmarks,
                'has_competitors' => $request->has_competitors,
                'competitor_details' => $request->competitor_details,
                'issues' => $request->issues ?? [],
                'observations' => $request->observations,
                'recommendations' => $request->recommendations,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'survey_datetime' => $request->survey_datetime,
                'weather' => $request->weather,
            ]),
        ]);
        
        $assignment->activities()->create([
            'user_id' => Auth::id(),
            'action' => 'uploaded_survey',
            'description' => 'Survey report uploaded by ' . Auth::user()->name,
        ]);
        
        return $this->successResponse('Survey report uploaded successfully!', $uploadedFiles, $request);
    }
    
    /**
     * Mark assignment as complete
     * Web + API
     */
    public function complete(Request $request, $id)
    {
        $staff = Auth::user();
        
        $assignment = $staff->assignments()->findOrFail($id);
        
        if ($assignment->status !== 'in_progress') {
            return $this->errorResponse('Only in-progress assignments can be completed', 400, $request);
        }
        
        $assignment->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress' => 100,
            'completion_notes' => $request->completion_notes,
        ]);
        
        $assignment->activities()->create([
            'user_id' => $staff->id,
            'action' => 'completed',
            'description' => 'Assignment completed by ' . $staff->name,
        ]);
        
        $message = 'Assignment marked as complete!';
        
        // API Response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $assignment->fresh()
            ]);
        }
        
        // Web Response
        return redirect()->route('staff.assignments.show', $id)
            ->with('success', $message);
    }
    
    /**
     * Send update to vendor/customer
     * Web + API
     */
    public function sendUpdate(Request $request, $id)
    {
        $staff = Auth::user();
        
        $assignment = $staff->assignments()->findOrFail($id);
        
        $validated = $request->validate([
            'message' => 'required|string',
            'notify_vendor' => 'nullable|boolean',
            'notify_customer' => 'nullable|boolean',
        ]);
        
        // Create update
        $update = $assignment->updates()->create([
            'user_id' => $staff->id,
            'message' => $validated['message'],
        ]);
        
        // Send notifications
        if ($request->notify_vendor && $assignment->booking) {
            // $assignment->booking->vendor->notify(new StaffUpdate($assignment, $update));
        }
        
        if ($request->notify_customer && $assignment->booking) {
            // $assignment->booking->customer->notify(new StaffUpdate($assignment, $update));
        }
        
        $assignment->activities()->create([
            'user_id' => $staff->id,
            'action' => 'sent_update',
            'description' => 'Update sent: ' . Str::limit($validated['message'], 50),
        ]);
        
        $message = 'Update sent successfully!';
        
        // API Response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $update
            ]);
        }
        
        // Web Response
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
    
    /**
     * Helper: Success response for Web + API
     */
    protected function successResponse($message, $data, Request $request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data
            ]);
        }
        
        return redirect()->back()->with('success', $message);
    }
    
    /**
     * Helper: Error response for Web + API
     */
    protected function errorResponse($message, $code, Request $request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], $code);
        }
        
        return redirect()->back()->with('error', $message);
    }
}
