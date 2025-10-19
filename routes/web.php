<?php

use App\Http\Controllers\GenerationHistoryController;
use App\Http\Controllers\InfographicController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

    // Generation history routes
    Route::prefix('history')->name('history.')->group(function () {
        Route::get('/', [GenerationHistoryController::class, 'index'])->name('index');
        Route::get('/{uuid}', [GenerationHistoryController::class, 'show'])->name('show');
        Route::delete('/{uuid}', [GenerationHistoryController::class, 'destroy'])->name('destroy');
        Route::post('/{uuid}/toggle-public', [GenerationHistoryController::class, 'togglePublic'])->name('togglePublic');
        Route::post('/{uuid}/retry', [GenerationHistoryController::class, 'retry'])->name('retry');
    });
});

require __DIR__.'/auth.php';
