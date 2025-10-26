<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pricing\AiProvider;
use App\Models\Pricing\ProviderPricing;
use App\Models\Pricing\CurrencyRate;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'providers_count' => AiProvider::count(),
            'active_providers' => AiProvider::where('is_active', true)->count(),
            'pricing_configs' => ProviderPricing::count(),
            'active_pricings' => ProviderPricing::where('is_active', true)->count(),
            'currency_rates' => CurrencyRate::count(),
            'users_count' => User::count(),
        ];

        $recentPricingUpdates = ProviderPricing::with('provider')
            ->latest('updated_at')
            ->take(10)
            ->get();

        $providers = AiProvider::withCount(['providerPricing', 'currencyRates'])
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPricingUpdates', 'providers'));
    }
}
