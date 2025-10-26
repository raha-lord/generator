<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pricing\AiProvider;
use App\Models\Pricing\CurrencyRate;
use Illuminate\Http\Request;

class CurrencyRateController extends Controller
{
    /**
     * Display a listing of currency rates
     */
    public function index()
    {
        $rates = CurrencyRate::with('provider')
            ->orderBy('provider_id')
            ->orderBy('from_unit')
            ->get();

        return view('admin.rates.index', compact('rates'));
    }

    /**
     * Show the form for creating a new rate
     */
    public function create()
    {
        $providers = AiProvider::all();

        return view('admin.rates.create', compact('providers'));
    }

    /**
     * Store a newly created rate
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'nullable|exists:pricing.ai_providers,id',
            'from_unit' => 'required|string|max:20',
            'to_currency' => 'required|string|max:10',
            'rate' => 'required|numeric|min:0',
            'markup_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);

        CurrencyRate::create($validated);

        return redirect()->route('admin.rates.index')
            ->with('success', 'Курс конвертации успешно создан');
    }

    /**
     * Show the form for editing the specified rate
     */
    public function edit(CurrencyRate $rate)
    {
        $providers = AiProvider::all();

        return view('admin.rates.edit', compact('rate', 'providers'));
    }

    /**
     * Update the specified rate
     */
    public function update(Request $request, CurrencyRate $rate)
    {
        $validated = $request->validate([
            'provider_id' => 'nullable|exists:pricing.ai_providers,id',
            'from_unit' => 'required|string|max:20',
            'to_currency' => 'required|string|max:10',
            'rate' => 'required|numeric|min:0',
            'markup_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);

        $rate->update($validated);

        return redirect()->route('admin.rates.index')
            ->with('success', 'Курс конвертации успешно обновлён');
    }

    /**
     * Remove the specified rate
     */
    public function destroy(CurrencyRate $rate)
    {
        $rate->delete();

        return redirect()->route('admin.rates.index')
            ->with('success', 'Курс конвертации успешно удалён');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(CurrencyRate $rate)
    {
        $rate->update(['is_active' => !$rate->is_active]);

        return back()->with('success', 'Статус курса изменён');
    }
}
