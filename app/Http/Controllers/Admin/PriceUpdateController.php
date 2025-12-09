<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use App\Models\PriceUpdateLog;
use App\Models\User;
use App\Services\PriceUpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PriceUpdateController extends Controller
{
    protected PriceUpdateService $priceUpdateService;

    public function __construct(PriceUpdateService $priceUpdateService)
    {
        $this->priceUpdateService = $priceUpdateService;
    }

    /**
     * Display price update dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $statistics = $this->priceUpdateService->getStatistics();
        $recentLogs = PriceUpdateLog::with(['admin', 'hoarding'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.price-updates.index', compact('statistics', 'recentLogs'));
    }

    /**
     * Show single price update form.
     *
     * @param int|null $hoardingId
     * @return \Illuminate\View\View
     */
    public function singleUpdate(?int $hoardingId = null)
    {
        $hoarding = $hoardingId ? Hoarding::findOrFail($hoardingId) : null;
        $hoardings = Hoarding::where('status', Hoarding::STATUS_ACTIVE)
            ->with('vendor')
            ->orderBy('title')
            ->get();

        return view('admin.price-updates.single', compact('hoarding', 'hoardings'));
    }

    /**
     * Process single price update.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeSingleUpdate(Request $request)
    {
        $validated = $request->validate([
            'hoarding_id' => 'required|exists:hoardings,id',
            'weekly_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $priceData = [];
            if ($validated['weekly_price'] !== null) {
                $priceData['weekly_price'] = $validated['weekly_price'];
            }
            $priceData['monthly_price'] = $validated['monthly_price'];

            $log = $this->priceUpdateService->updateSinglePrice(
                $validated['hoarding_id'],
                $priceData,
                auth()->id(),
                $validated['reason'] ?? null
            );

            return redirect()
                ->route('admin.price-updates.index')
                ->with('success', 'Price updated successfully for hoarding #' . $validated['hoarding_id']);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update price: ' . $e->getMessage());
        }
    }

    /**
     * Show bulk price update form.
     *
     * @return \Illuminate\View\View
     */
    public function bulkUpdate()
    {
        $vendors = User::role('vendor')->orderBy('name')->get();
        $types = Hoarding::getTypes();
        $statuses = Hoarding::getStatuses();

        return view('admin.price-updates.bulk', compact('vendors', 'types', 'statuses'));
    }

    /**
     * Preview bulk update.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewBulkUpdate(Request $request)
    {
        $criteria = $request->only([
            'vendor_id', 'type', 'status', 'city', 'area', 'property_type', 'min_price', 'max_price'
        ]);

        $query = Hoarding::query();
        
        if (!empty($criteria['vendor_id'])) {
            $query->where('vendor_id', $criteria['vendor_id']);
        }
        if (!empty($criteria['type'])) {
            $query->where('type', $criteria['type']);
        }
        if (!empty($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }
        if (!empty($criteria['city'])) {
            $query->where('address', 'like', '%' . $criteria['city'] . '%');
        }
        if (!empty($criteria['area'])) {
            $query->where('address', 'like', '%' . $criteria['area'] . '%');
        }
        if (!empty($criteria['property_type'])) {
            $query->where(function ($q) use ($criteria) {
                $q->where('title', 'like', '%' . $criteria['property_type'] . '%')
                  ->orWhere('description', 'like', '%' . $criteria['property_type'] . '%');
            });
        }
        if (!empty($criteria['min_price'])) {
            $query->where('monthly_price', '>=', $criteria['min_price']);
        }
        if (!empty($criteria['max_price'])) {
            $query->where('monthly_price', '<=', $criteria['max_price']);
        }

        $hoardings = $query->with('vendor')->get();
        $count = $hoardings->count();

        $preview = $hoardings->take(10)->map(function ($hoarding) {
            return [
                'id' => $hoarding->id,
                'title' => $hoarding->title,
                'vendor' => $hoarding->vendor->name ?? 'N/A',
                'current_weekly_price' => $hoarding->weekly_price,
                'current_monthly_price' => $hoarding->monthly_price,
                'address' => $hoarding->address,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $count,
            'preview' => $preview,
        ]);
    }

    /**
     * Process bulk price update.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'nullable|exists:users,id',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'property_type' => 'nullable|string|max:255',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'update_method' => 'required|in:fixed,percentage,increment,decrement',
            'update_value' => 'required|numeric',
            'price_type' => 'required|in:weekly,monthly,both',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $criteria = $request->only([
                'vendor_id', 'type', 'status', 'city', 'area', 'property_type', 'min_price', 'max_price'
            ]);

            $updateConfig = [
                'method' => $validated['update_method'],
                'value' => $validated['update_value'],
                'price_type' => $validated['price_type'],
            ];

            $result = $this->priceUpdateService->bulkUpdatePrices(
                $criteria,
                $updateConfig,
                auth()->id(),
                $validated['reason'] ?? null
            );

            return redirect()
                ->route('admin.price-updates.index')
                ->with('success', 'Bulk update completed! Updated ' . $result['updated_count'] . ' hoardings. Batch ID: ' . $result['batch_id']);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to perform bulk update: ' . $e->getMessage());
        }
    }

    /**
     * Show update logs.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function logs(Request $request)
    {
        $filters = $request->only(['update_type', 'admin_id', 'batch_id', 'date_from', 'date_to']);
        $logs = $this->priceUpdateService->getUpdateLogs($filters, 20);
        
        $admins = User::role('admin')->orWhere->role('super_admin')->orderBy('name')->get();
        $batches = PriceUpdateLog::whereNotNull('batch_id')
            ->distinct()
            ->pluck('batch_id')
            ->take(50);

        return view('admin.price-updates.logs', compact('logs', 'admins', 'batches', 'filters'));
    }

    /**
     * Show single log detail.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showLog(int $id)
    {
        $log = PriceUpdateLog::with(['admin', 'hoarding'])->findOrFail($id);
        
        // If it's a bulk update, get all logs in the batch
        $batchLogs = null;
        if ($log->batch_id) {
            $batchLogs = PriceUpdateLog::where('batch_id', $log->batch_id)
                ->with(['hoarding'])
                ->get();
        }

        return view('admin.price-updates.log-detail', compact('log', 'batchLogs'));
    }
}
