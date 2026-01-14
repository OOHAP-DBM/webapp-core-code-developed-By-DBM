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


    class AdminHoardingController extends Controller
    {
    /**
     * Show all hoardings owned by admin (no vendor_id).
     */
    public function adminHoardings(Request $request): View
    {
        $perPage = 10;
        $hoardings = Hoarding::whereNull('vendor_id')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $completionService = app(\App\Services\HoardingCompletionService::class);
        $hoardings->getCollection()->transform(function ($h) use ($completionService) {
            return (object) [
                'id' => $h->id,
                'title' => $h->title ?? $h->name,
                'type' => strtoupper($h->hoarding_type),
                'address' => $h->address,
                'status' => $h->status,
                'completion' => $completionService->calculateCompletion($h),
            ];
        });

        return view('hoardings.admin.admin-hoardings', [
            'hoardings' => $hoardings
        ]);
    }
    }
