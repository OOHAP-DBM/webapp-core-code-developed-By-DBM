<?php
 namespace Modules\Hoardings\Http\Controllers\Admin;

    use App\Http\Controllers\Controller;
    use App\Models\Hoarding;
    use Modules\Mail\HoardingPublishedMail;
    use Modules\DOOH\Models\DOOHScreen;
    use Illuminate\Http\Request;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\View\View;
    use Illuminate\Support\Facades\Mail;
    use Illuminate\Support\Facades\DB;

    class AdminHoardingController extends Controller
    {
        private function adminOwnedHoardingsQuery()
        {
            $adminUserIds = \App\Models\User::where('active_role', 'admin')->pluck('id');

            return Hoarding::where(function ($query) use ($adminUserIds) {
                $query->whereNull('vendor_id')
                    ->orWhereIn('vendor_id', $adminUserIds);
            });
        }

    /**
     * Show all hoardings owned by admin (no vendor_id).
     */
    public function adminHoardings(Request $request): View
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,suspended,draft,pending_approval',
            'type' => 'nullable|in:ooh,dooh',
            'recommended' => 'nullable|in:yes,no',
            'per_page' => 'nullable|in:10,25,50',
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = $this->adminOwnedHoardingsQuery();

        if (!empty($validated['search'])) {
            $search = trim($validated['search']);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%');
            });
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['type'])) {
            $query->where('hoarding_type', strtolower($validated['type']));
        }

        if (!empty($validated['recommended'])) {
            $query->where('is_recommended', $validated['recommended'] === 'yes');
        }

        $hoardings = $query
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
        $completionService = app(\App\Services\HoardingCompletionService::class);
        $hoardings->getCollection()->transform(function ($h) use ($completionService) {
            return (object) [
                'id' => $h->id,
                'title' => $h->title ?? $h->name,
                'type' => strtoupper($h->hoarding_type),
                'address' => $h->address,
                'status' => $h->status,
                'is_recommended' => (bool) $h->is_recommended,
                'completion' => $completionService->calculateCompletion($h),
            ];
        });
        return view('hoardings.admin.admin-hoardings', [
            'hoardings' => $hoardings,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'status' => $validated['status'] ?? '',
                'type' => $validated['type'] ?? '',
                'recommended' => $validated['recommended'] ?? '',
                'per_page' => (string) $perPage,
            ],
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $hoarding = $this->adminOwnedHoardingsQuery()->findOrFail($id);
        $hoarding->status = $validated['status'];
        $hoarding->save();

        return back()->with('success', 'Hoarding status updated successfully.');
    }

    public function destroy(int $id)
    {
        $hoarding = $this->adminOwnedHoardingsQuery()->findOrFail($id);
        $hoarding->delete();

        return back()->with('success', 'Hoarding deleted successfully.');
    }

    public function updateRecommendation(Request $request, int $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:recommend,unrecommend',
        ]);

        $hoarding = $this->adminOwnedHoardingsQuery()->findOrFail($id);
        $shouldRecommend = $validated['action'] === 'recommend';
        $hoarding->is_recommended = $shouldRecommend;
        $hoarding->save();

        return back()->with('success', $shouldRecommend
            ? 'Hoarding recommended successfully.'
            : 'Hoarding unrecommended successfully.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,suspend,delete,recommend,unrecommend',
            'ids' => 'required|array|min:2',
            'ids.*' => 'required|integer',
        ]);

        $ids = array_values(array_unique($validated['ids']));
        $query = $this->adminOwnedHoardingsQuery()->whereIn('id', $ids);
        $hoardings = $query->get(['id']);

        if ($hoardings->isEmpty()) {
            return back()->with('error', 'No valid hoardings selected.');
        }

        if ($hoardings->count() !== count($ids)) {
            return back()->with('error', 'Some selected hoardings are invalid or not editable. Please refresh and try again.');
        }

        $action = $validated['action'];
        $affectedCount = 0;

        DB::transaction(function () use ($query, $action, &$affectedCount) {
            switch ($action) {
                case 'activate':
                    $affectedCount = $query->update(['status' => 'active']);
                    break;
                case 'deactivate':
                    $affectedCount = $query->update(['status' => 'inactive']);
                    break;
                case 'suspend':
                    $affectedCount = $query->update(['status' => 'suspended']);
                    break;
                case 'recommend':
                    $affectedCount = $query->update(['is_recommended' => true]);
                    break;
                case 'unrecommend':
                    $affectedCount = $query->update(['is_recommended' => false]);
                    break;
                case 'delete':
                    $affectedCount = $query->delete();
                    break;
            }
        });

        return back()->with('success', $affectedCount . ' hoarding(s) updated successfully.');
    }

    }
