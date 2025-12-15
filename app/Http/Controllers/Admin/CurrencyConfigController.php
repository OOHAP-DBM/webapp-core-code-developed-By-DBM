<?php

namespace App\Http\Controllers\Admin;

use App\Models\CurrencyConfig;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

/**
 * PROMPT 109: Currency Configuration Admin Controller
 */
class CurrencyConfigController extends Controller
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Display listing of currencies
     */
    public function index()
    {
        $currencies = CurrencyConfig::orderBy('is_default', 'desc')
            ->orderBy('is_active', 'desc')
            ->orderBy('code')
            ->get();

        $defaultCurrency = CurrencyConfig::getDefault();

        return view('admin.currency.index', compact('currencies', 'defaultCurrency'));
    }

    /**
     * Show form for creating new currency
     */
    public function create()
    {
        return view('admin.currency.create');
    }

    /**
     * Store new currency
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currency_configs,code',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'symbol_position' => 'required|in:before,after',
            'decimal_separator' => 'required|string|size:1',
            'thousand_separator' => 'required|string|size:1',
            'decimal_places' => 'required|integer|min:0|max:4',
            'exchange_rate' => 'required|numeric|min:0.000001|max:999999',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'country_code' => 'nullable|string|size:2',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        $currency = CurrencyConfig::create($validated);

        return redirect()
            ->route('admin.currency.index')
            ->with('success', "Currency {$currency->code} created successfully!");
    }

    /**
     * Show form for editing currency
     */
    public function edit(CurrencyConfig $currency)
    {
        return view('admin.currency.edit', compact('currency'));
    }

    /**
     * Update currency
     */
    public function update(Request $request, CurrencyConfig $currency)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'symbol_position' => 'required|in:before,after',
            'decimal_separator' => 'required|string|size:1',
            'thousand_separator' => 'required|string|size:1',
            'decimal_places' => 'required|integer|min:0|max:4',
            'exchange_rate' => 'required|numeric|min:0.000001|max:999999',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'country_code' => 'nullable|string|size:2',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active');

        $currency->update($validated);

        return redirect()
            ->route('admin.currency.index')
            ->with('success', "Currency {$currency->code} updated successfully!");
    }

    /**
     * Delete currency
     */
    public function destroy(CurrencyConfig $currency)
    {
        if ($currency->is_default) {
            return back()->with('error', 'Cannot delete default currency. Set another currency as default first.');
        }

        $code = $currency->code;
        $currency->delete();

        return redirect()
            ->route('admin.currency.index')
            ->with('success', "Currency {$code} deleted successfully!");
    }

    /**
     * Set currency as default
     */
    public function setDefault(CurrencyConfig $currency)
    {
        $this->currencyService->setDefault($currency->code);

        return back()->with('success', "Currency {$currency->code} set as default!");
    }

    /**
     * Toggle currency active status
     */
    public function toggleActive(CurrencyConfig $currency)
    {
        if ($currency->is_default && $currency->is_active) {
            return back()->with('error', 'Cannot deactivate default currency.');
        }

        $currency->update(['is_active' => !$currency->is_active]);

        $status = $currency->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Currency {$currency->code} {$status} successfully!");
    }

    /**
     * Update exchange rates (bulk)
     */
    public function updateRates(Request $request)
    {
        $validated = $request->validate([
            'rates' => 'required|array',
            'rates.*' => 'required|numeric|min:0.000001',
        ]);

        foreach ($validated['rates'] as $code => $rate) {
            CurrencyConfig::where('code', $code)->update(['exchange_rate' => $rate]);
        }

        return back()->with('success', 'Exchange rates updated successfully!');
    }

    /**
     * Preview currency formatting
     */
    public function preview(Request $request)
    {
        $amount = $request->input('amount', 1234.56);
        $currencyCode = $request->input('currency_code');

        try {
            $formatted = $this->currencyService->formatWith($amount, $currencyCode);
            return response()->json(['formatted' => $formatted]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
