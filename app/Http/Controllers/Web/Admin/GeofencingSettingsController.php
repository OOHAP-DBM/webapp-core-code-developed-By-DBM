<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Settings\Services\SettingsService;

class GeofencingSettingsController extends Controller
{
    protected SettingsService $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Show geo-fencing settings page
     */
    public function index()
    {
        // Get all geofencing settings
        $settings = [
            'pod.geofence_radius_meters' => $this->settings->get('pod.geofence_radius_meters', 100),
            'pod.strict_geofence_validation' => $this->settings->get('pod.strict_geofence_validation', true),
            'pod.require_gps_coordinates' => $this->settings->get('pod.require_gps_coordinates', true),
            'pod.max_gps_accuracy_meters' => $this->settings->get('pod.max_gps_accuracy_meters', 50),
            'pod.log_geofence_violations' => $this->settings->get('pod.log_geofence_violations', true),
            'pod.show_distance_to_mounter' => $this->settings->get('pod.show_distance_to_mounter', true),
            'geofencing.alert_threshold_meters' => $this->settings->get('geofencing.alert_threshold_meters', 150),
            'geofencing.enable_for_dismounting' => $this->settings->get('geofencing.enable_for_dismounting', true),
            'geofencing.auto_approve_within_radius' => $this->settings->get('geofencing.auto_approve_within_radius', 50),
        ];

        // Get statistics
        $stats = [
            'total_pods' => BookingProof::count(),
            'approved_pods' => BookingProof::where('status', 'approved')->count(),
            'violations' => BookingProof::whereNotNull('distance_from_hoarding')
                ->where('distance_from_hoarding', '>', $settings['pod.geofence_radius_meters'])
                ->count(),
            'avg_distance' => round(BookingProof::whereNotNull('distance_from_hoarding')
                ->avg('distance_from_hoarding')),
        ];

        return view('admin.settings.geofencing', compact('settings', 'stats'));
    }

    /**
     * Update geo-fencing settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'geofence_radius' => 'required|integer|min:10|max:1000',
            'strict_validation' => 'nullable|boolean',
            'require_gps' => 'nullable|boolean',
            'gps_accuracy' => 'nullable|integer|min:5|max:200',
            'log_violations' => 'nullable|boolean',
            'show_distance' => 'nullable|boolean',
            'alert_threshold' => 'nullable|integer|min:50|max:500',
            'enable_dismounting' => 'nullable|boolean',
            'auto_approve_radius' => 'nullable|integer|min:0|max:200',
        ]);

        try {
            DB::beginTransaction();

            // Update all settings
            $this->settings->set(
                'pod.geofence_radius_meters',
                $validated['geofence_radius'],
                'integer',
                null,
                'Maximum distance in meters from hoarding location for POD uploads',
                'geofencing'
            );

            $this->settings->set(
                'pod.strict_geofence_validation',
                $request->has('strict_validation') ? 1 : 0,
                'boolean',
                null,
                'Enable strict geo-fence validation',
                'geofencing'
            );

            $this->settings->set(
                'pod.require_gps_coordinates',
                $request->has('require_gps') ? 1 : 0,
                'boolean',
                null,
                'Require GPS coordinates for all POD uploads',
                'geofencing'
            );

            $this->settings->set(
                'pod.max_gps_accuracy_meters',
                $validated['gps_accuracy'] ?? 50,
                'integer',
                null,
                'Maximum GPS accuracy threshold in meters',
                'geofencing'
            );

            $this->settings->set(
                'pod.log_geofence_violations',
                $request->has('log_violations') ? 1 : 0,
                'boolean',
                null,
                'Log all geo-fence validation violations',
                'geofencing'
            );

            $this->settings->set(
                'pod.show_distance_to_mounter',
                $request->has('show_distance') ? 1 : 0,
                'boolean',
                null,
                'Show real-time distance to hoarding location',
                'geofencing'
            );

            $this->settings->set(
                'geofencing.alert_threshold_meters',
                $validated['alert_threshold'] ?? 150,
                'integer',
                null,
                'Distance threshold for alerting admin about suspicious uploads',
                'geofencing'
            );

            $this->settings->set(
                'geofencing.enable_for_dismounting',
                $request->has('enable_dismounting') ? 1 : 0,
                'boolean',
                null,
                'Apply geo-fence validation for dismounting proof',
                'geofencing'
            );

            $this->settings->set(
                'geofencing.auto_approve_within_radius',
                $validated['auto_approve_radius'] ?? 50,
                'integer',
                null,
                'Auto-approve POD if within this radius',
                'geofencing'
            );

            // Clear settings cache
            $this->settings->clearCache();

            DB::commit();

            return redirect()
                ->route('admin.settings.geofencing')
                ->with('success', 'Geo-fencing settings updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Get geo-fence violations report
     */
    public function violations(Request $request)
    {
        $days = $request->input('days', 30);
        $maxRadius = $this->settings->get('pod.geofence_radius_meters', 100);

        $violations = BookingProof::with(['booking.hoarding', 'uploader'])
            ->whereNotNull('distance_from_hoarding')
            ->where('distance_from_hoarding', '>', $maxRadius)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.settings.geofencing-violations', compact('violations', 'maxRadius', 'days'));
    }
}
