<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route de test pour l'authentification Gmail
Route::get('/test-gmail', function () {
    return view('test-gmail');
});

// Routes web pour l'authentification Gmail (avec sessions)
Route::prefix('auth/gmail')->middleware('web')->group(function () {
    Route::get('/redirect', [App\Http\Controllers\Api\GmailAuthController::class, 'webRedirect']);
    Route::get('/callback', [App\Http\Controllers\Api\GmailAuthController::class, 'webCallback']);
    Route::get('/frontend-redirect', [App\Http\Controllers\Api\GmailAuthController::class, 'frontendRedirect']);
});

// Route web qui correspond à l'URI configurée dans Google Console
Route::middleware('web')->get('/api/auth/gmail/callback', [App\Http\Controllers\Api\GmailAuthController::class, 'frontendWebCallback']);
