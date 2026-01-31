<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GmailAuthController;

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

// Route pour prévisualiser l'email de vérification (non protégée)
Route::get('/preview-verify-email', function () {
    // Création d'un utilisateur factice pour la prévisualisation
    $user = new stdClass();
    $user->name = "Manangamanana Valloys";
    $user->first_name = "Manangamanana";
    $user->email = "manangamanana@example.com";

    // URL de vérification factice
    $verificationUrl = url('/verify-email/token-exemple-123456');

    return view('emails.verify-email', [
        'user' => $user,
        'verificationUrl' => $verificationUrl
    ]);
});

// Routes web pour l'authentification Gmail (avec sessions)
Route::prefix('auth/gmail')->middleware('web')->group(function () {
    Route::get('/redirect', [GmailAuthController::class, 'webRedirect']);
    Route::get('/callback', [GmailAuthController::class, 'webCallback']);
    Route::get('/frontend-redirect', [GmailAuthController::class, 'frontendRedirect']);
});

// Route web qui correspond à l'URI configurée dans Google Console
Route::middleware('web')->get('/api/auth/gmail/callback', [GmailAuthController::class, 'frontendWebCallback']);
