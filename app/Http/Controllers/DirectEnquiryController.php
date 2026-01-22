<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DirectEnquiry;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Mail\AdminDirectEnquiryMail;
use App\Notifications\AdminDirectEnquiryNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
class DirectEnquiryController extends Controller
{
    public function showForm()
    {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        Session::put('captcha_answer', $num1 + $num2);

        return view('welcome', compact('num1', 'num2'));
    }


    /**
     * Handle the form submission
     */
    public function store(Request $request)
    {
        // 1. Create Validator Instance (Manual instance is required for AJAX)
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255|min:3',
            'email'             => 'required|email|max:255',
            'phone'             => 'required|numeric|digits:10',
            'location_city'     => 'required|string|max:255',
            'hoarding_type'     => 'required|string',
            'hoarding_location' => 'required|string',
            'remarks'           => 'nullable|string|max:1000',
            'captcha'           => 'required|numeric',
        ]);

        // 2. Return JSON if validation fails (Prevents 302 Redirect)
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // 4. Save to Database
            $data = $validator->validated();
            unset($data['captcha']);

            // FIX: Assign the result of create() to the variable $enquiry
            $enquiry = DirectEnquiry::create($data);

            // 5. Send Email
            // If your SMTP settings in .env are wrong, this is where the 500 error triggers
            Mail::to('admin@oohapp.com')->send(new AdminDirectEnquiryMail($enquiry));

            // 6. Send Dashboard Notification
            $admins = User::where('active_role', 'admin')->get();
            Notification::send($admins, new AdminDirectEnquiryNotification($enquiry));

            // 7. Success Flow
            Session::forget('captcha_answer');

            return response()->json([
                'status' => 'success',
                'message' => 'Thank you! Your enquiry has been submitted successfully.'
            ], 200);
        } catch (\Exception $e) {
            // This logs the actual error (e.g., "Undefined variable $enquiry" or "Connection refused")
            \Log::error("Direct Enquiry Error: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage() // TEMPORARY: Add $e->getMessage() to see the error in your browser console
            ], 500);
        }
    }
    
}
