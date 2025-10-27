<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CurrencyRateController;
use App\Http\Controllers\Admin\ProviderPricingController;
use App\Http\Controllers\ChatViewController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GenerationHistoryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InfographicController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// File routes (public access with internal permission check)
Route::prefix('file')->name('file.')->group(function () {
    Route::get('/{uuid}', [FileController::class, 'show'])->name('show');
    Route::get('/{uuid}/download', [FileController::class, 'download'])->name('download');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Infographic generation routes
    Route::prefix('infographic')->name('infographic.')->group(function () {
        Route::get('/create', [InfographicController::class, 'create'])->name('create');
        Route::post('/', [InfographicController::class, 'store'])->name('store');
        Route::get('/{uuid}', [InfographicController::class, 'show'])->name('show');
    });

    // Image generation routes
    Route::prefix('image')->name('image.')->group(function () {
        Route::get('/create', [ImageController::class, 'create'])->name('create');
        Route::post('/', [ImageController::class, 'store'])->name('store');
        Route::get('/{uuid}', [ImageController::class, 'show'])->name('show');
    });

    // Generation history routes
    Route::prefix('history')->name('history.')->group(function () {
        Route::get('/', [GenerationHistoryController::class, 'index'])->name('index');
        Route::get('/{uuid}', [GenerationHistoryController::class, 'show'])->name('show');
        Route::delete('/{uuid}', [GenerationHistoryController::class, 'destroy'])->name('destroy');
        Route::post('/{uuid}/toggle-public', [GenerationHistoryController::class, 'togglePublic'])->name('togglePublic');
        Route::post('/{uuid}/retry', [GenerationHistoryController::class, 'retry'])->name('retry');
    });

    // Chat routes
    Route::prefix('chats')->name('chats.')->group(function () {
        Route::get('/', [ChatViewController::class, 'index'])->name('index');
        Route::get('/create', [ChatViewController::class, 'create'])->name('create');
        Route::get('/{uuid}', [ChatViewController::class, 'show'])->name('show');
    });
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Provider Pricing Management
    Route::resource('pricing', ProviderPricingController::class);
    Route::post('pricing/{pricing}/toggle-active', [ProviderPricingController::class, 'toggleActive'])
        ->name('pricing.toggle-active');

    // Currency Rate Management
    Route::resource('rates', CurrencyRateController::class);
    Route::post('rates/{rate}/toggle-active', [CurrencyRateController::class, 'toggleActive'])
        ->name('rates.toggle-active');
});

require __DIR__.'/auth.php';
