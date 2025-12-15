<?php

namespace App\Http\Controllers\Admin;

use App\Models\TaxConfiguration;
use App\Services\TaxConfigurationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * PROMPT 109: Tax Configuration Admin Controller
 */
class TaxConfigController extends Controller
{
    protected TaxConfigurationService $taxConfigService;

    public function __construct(TaxConfigurationService $taxConfigService)
    {
        $this->taxConfigService = $taxConfigService;
    }

    /**
     * Display tax configuration dashboard
     */
    public function index()
    {
        $gstConfigs = TaxConfiguration::byType(TaxConfiguration::TYPE_GST)
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        $tcsConfigs = TaxConfiguration::byType(TaxConfiguration::TYPE_TCS)
            ->orderBy('key')
            ->get();

        $tdsConfigs = TaxConfiguration::byType(TaxConfiguration::TYPE_TDS)
            ->orderBy('key')
            ->get();

        $generalConfigs = TaxConfiguration::byType(TaxConfiguration::TYPE_GENERAL)
            ->orderBy('key')
            ->get();

        $allConfigurations = $this->taxConfigService->getAllConfigurations();

        return view('admin.tax-config.index', compact(
            'gstConfigs',
            'tcsConfigs',
            'tdsConfigs',
            'generalConfigs',
            'allConfigurations'
        ));
    }

    /**
     * Show form for editing configuration
     */
    public function edit(TaxConfiguration $taxConfig)
    {
        return view('admin.tax-config.edit', compact('taxConfig'));
    }

    /**
     * Update configuration
     */
    public function update(Request $request, TaxConfiguration $taxConfig)
    {
        // Validate based on data type
        $rules = $this->getValidationRules($taxConfig);
        $validated = $request->validate($rules);

        // Update value
        $value = $validated['value'];

        // Type conversion
        if ($taxConfig->data_type === TaxConfiguration::DATA_BOOLEAN) {
            $value = $request->boolean('value');
        } elseif ($taxConfig->data_type === TaxConfiguration::DATA_ARRAY) {
            $value = is_array($validated['value']) ? $validated['value'] : json_decode($validated['value'], true);
        }

        $taxConfig->setTypedValue($value);
        $taxConfig->is_active = $request->boolean('is_active', true);
        
        if ($request->filled('description')) {
            $taxConfig->description = $request->input('description');
        }

        $taxConfig->save();

        return redirect()
            ->route('admin.tax-config.index')
            ->with('success', "Configuration '{$taxConfig->name}' updated successfully!");
    }

    /**
     * Quick update (AJAX)
     */
    public function quickUpdate(Request $request, TaxConfiguration $taxConfig)
    {
        $value = $request->input('value');

        // Type conversion
        if ($taxConfig->data_type === TaxConfiguration::DATA_BOOLEAN) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } elseif ($taxConfig->data_type === TaxConfiguration::DATA_INTEGER) {
            $value = (int) $value;
        } elseif ($taxConfig->data_type === TaxConfiguration::DATA_FLOAT) {
            $value = (float) $value;
        }

        // Validate if rules exist
        if (!$taxConfig->validate($value)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed'
            ], 422);
        }

        $taxConfig->setTypedValue($value);
        $taxConfig->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuration updated',
            'formatted_value' => $this->formatValue($taxConfig),
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggleActive(TaxConfiguration $taxConfig)
    {
        $taxConfig->update(['is_active' => !$taxConfig->is_active]);

        $status = $taxConfig->is_active ? 'enabled' : 'disabled';
        return back()->with('success', "Configuration {$status} successfully!");
    }

    /**
     * Test tax calculation
     */
    public function testCalculation(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'customer_state_code' => 'nullable|string|size:2',
            'vendor_state_code' => 'nullable|string|size:2',
            'transaction_type' => 'required|string|in:purchase_order,invoice,payout',
        ]);

        $taxResult = $this->taxConfigService->calculateCompleteTax(
            $validated['amount'],
            [
                'transaction_type' => $validated['transaction_type'],
                'customer_state_code' => $request->input('customer_state_code', 'MH'),
                'vendor_state_code' => $request->input('vendor_state_code', 'MH'),
                'has_gstin' => true,
            ]
        );

        $summary = $this->taxConfigService->getTaxSummary($taxResult);

        return response()->json([
            'success' => true,
            'result' => $taxResult,
            'summary' => $summary,
        ]);
    }

    /**
     * Export configurations
     */
    public function export()
    {
        $configs = TaxConfiguration::all();

        $export = $configs->map(function ($config) {
            return [
                'key' => $config->key,
                'name' => $config->name,
                'value' => $config->getTypedValue(),
                'type' => $config->config_type,
                'data_type' => $config->data_type,
                'is_active' => $config->is_active,
            ];
        });

        return response()->json($export);
    }

    /**
     * Reset to defaults
     */
    public function resetDefaults(Request $request)
    {
        // Run seeder
        \Artisan::call('db:seed', ['--class' => 'TaxConfigurationSeeder', '--force' => true]);

        return back()->with('success', 'Tax configurations reset to defaults!');
    }

    /**
     * Get validation rules for configuration
     */
    protected function getValidationRules(TaxConfiguration $taxConfig): array
    {
        $rules = ['value' => 'required'];

        switch ($taxConfig->data_type) {
            case TaxConfiguration::DATA_BOOLEAN:
                $rules['value'] = 'required|boolean';
                break;
            case TaxConfiguration::DATA_INTEGER:
                $rules['value'] = 'required|integer';
                break;
            case TaxConfiguration::DATA_FLOAT:
                $rules['value'] = 'required|numeric';
                break;
            case TaxConfiguration::DATA_ARRAY:
                $rules['value'] = 'required|array';
                break;
            default:
                $rules['value'] = 'required|string';
        }

        // Apply custom validation rules if defined
        if (!empty($taxConfig->validation_rules)) {
            foreach ($taxConfig->validation_rules as $rule => $ruleValue) {
                if ($rule === 'min') {
                    $rules['value'] .= "|min:{$ruleValue}";
                } elseif ($rule === 'max') {
                    $rules['value'] .= "|max:{$ruleValue}";
                } elseif ($rule === 'in') {
                    $rules['value'] .= '|in:' . implode(',', $ruleValue);
                }
            }
        }

        return $rules;
    }

    /**
     * Format value for display
     */
    protected function formatValue(TaxConfiguration $taxConfig): string
    {
        $value = $taxConfig->getTypedValue();

        if ($taxConfig->data_type === TaxConfiguration::DATA_BOOLEAN) {
            return $value ? 'Yes' : 'No';
        } elseif ($taxConfig->data_type === TaxConfiguration::DATA_ARRAY) {
            return json_encode($value);
        } elseif ($taxConfig->data_type === TaxConfiguration::DATA_FLOAT) {
            return number_format($value, 2);
        }

        return (string) $value;
    }
}
