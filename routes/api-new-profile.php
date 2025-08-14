<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NewProfileController;

/*
|--------------------------------------------------------------------------
| API Routes for New Profile Structure
|--------------------------------------------------------------------------
|
| These routes handle the new unified profile structure.
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // New profile routes
    Route::get('/profile/new', [NewProfileController::class, 'getProfile']);
    Route::put('/profile/new', [NewProfileController::class, 'updateProfile']);
    Route::post('/profile/new/complete', [NewProfileController::class, 'completeProfile']);
    Route::get('/profile/new/completion', [NewProfileController::class, 'getCompletionStatus']);
    Route::post('/profile/new/avatar', [NewProfileController::class, 'uploadAvatar']);
    Route::post('/profile/new/portfolio', [NewProfileController::class, 'uploadPortfolioItem']);
    Route::delete('/profile/new/portfolio/{id}', [NewProfileController::class, 'deletePortfolioItem']);
    Route::put('/profile/new/availability', [NewProfileController::class, 'updateAvailability']);
});
