<?php

namespace Modules\Admin\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Services\SettingsService;

class SettingController extends Controller
{
    /**
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * SettingController constructor.
     *
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display settings management page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = $this->settingsService->getAllWithMetadata();
        
        // Group settings by their group field
        $groupedSettings = $settings->groupBy('group');

        return view('admin.settings.index', compact('groupedSettings'));
    }

    /**
     * Update settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try {
            $settings = $request->input('settings', []);
            
            // Update each setting
            foreach ($settings as $key => $value) {
                $setting = $this->settingsService->getAllWithMetadata()->firstWhere('key', $key);
                
                if ($setting) {
                    $this->settingsService->set(
                        $key,
                        $value,
                        $setting->type,
                        null, // Global settings (no tenant_id)
                        $setting->description,
                        $setting->group
                    );
                }
            }

            return redirect()
                ->route('admin.settings.index')
                ->with('success', 'Settings updated successfully!');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update settings: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Reset settings to default values.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset()
    {
        try {
            // Clear all cache
            $this->settingsService->clearCache();
            
            // Re-run seeder
            \Artisan::call('db:seed', ['--class' => 'SettingsSeeder']);

            return redirect()
                ->route('admin.settings.index')
                ->with('success', 'Settings reset to default values successfully!');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    /**
     * Clear settings cache.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache()
    {
        try {
            $this->settingsService->clearCache();

            return redirect()
                ->route('admin.settings.index')
                ->with('success', 'Settings cache cleared successfully!');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }
}
