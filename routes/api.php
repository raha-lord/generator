<?php

use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Chat endpoints
    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::post('/', [ChatController::class, 'store']);
        Route::get('/{uuid}', [ChatController::class, 'show']);
        Route::delete('/{uuid}', [ChatController::class, 'destroy']);

        // Chat actions
        Route::post('/{uuid}/message', [ChatController::class, 'sendMessage']);
        Route::post('/{uuid}/continue', [ChatController::class, 'continueWorkflow']);
        Route::patch('/{uuid}/title', [ChatController::class, 'updateTitle']);
    });
});
