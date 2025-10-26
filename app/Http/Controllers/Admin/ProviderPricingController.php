<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pricing\AiProvider;
use App\Models\Pricing\ProviderPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProviderPricingController extends Controller
{
    /**
     * Display a listing of provider pricings
     */
    public function index()
    {
        $pricings = ProviderPricing::with('provider')
            ->orderBy('provider_id')
            ->orderBy('service_type')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.pricing.index', compact('pricings'));
    }

    /**
     * Show the form for creating a new pricing
     */
    public function create()
    {
        $providers = AiProvider::where('is_active', true)->get();

        return view('admin.pricing.create', compact('providers'));
    }

    /**
     * Store a newly created pricing
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:pricing.ai_providers,id',
            'service_type' => 'required|string|max:50',
            'display_name' => 'required|string|max:200',
            'token_cost' => 'required|numeric|min:0',
            'conditions' => 'nullable|json',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Generate pricing_key if not provided
        $validated['pricing_key'] = $this->generatePricingKey(
            $validated['provider_id'],
            $validated['service_type'],
            $validated['display_name']
        );

        // Parse JSON conditions
        if (!empty($validated['conditions'])) {
            $validated['conditions'] = json_decode($validated['conditions'], true);
        }

        $pricing = ProviderPricing::create($validated);

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Прайсинг успешно создан');
    }

    /**
     * Show the form for editing the specified pricing
     */
    public function edit(ProviderPricing $pricing)
    {
        $providers = AiProvider::all();

        return view('admin.pricing.edit', compact('pricing', 'providers'));
    }

    /**
     * Update the specified pricing
     */
    public function update(Request $request, ProviderPricing $pricing)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:pricing.ai_providers,id',
            'service_type' => 'required|string|max:50',
            'display_name' => 'required|string|max:200',
            'token_cost' => 'required|numeric|min:0',
            'conditions' => 'nullable|json',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Parse JSON conditions
        if (!empty($validated['conditions'])) {
            $validated['conditions'] = json_decode($validated['conditions'], true);
        }

        $pricing->update($validated);

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Прайсинг успешно обновлён');
    }

    /**
     * Remove the specified pricing
     */
    public function destroy(ProviderPricing $pricing)
    {
        $pricing->delete();

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Прайсинг успешно удалён');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(ProviderPricing $pricing)
    {
        $pricing->update(['is_active' => !$pricing->is_active]);

        return back()->with('success', 'Статус прайсинга изменён');
    }

    /**
     * Generate unique pricing key
     */
    private function generatePricingKey(int $providerId, string $serviceType, string $displayName): string
    {
        $provider = AiProvider::find($providerId);
        $slug = Str::slug($displayName);

        return "{$provider->name}_{$serviceType}_{$slug}";
    }
}
