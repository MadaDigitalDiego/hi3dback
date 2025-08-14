<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OpenOfferController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\ProfessionalController;
use App\Http\Controllers\Api\ServiceOfferController;
use App\Http\Controllers\Api\DashboardProjectController;

// Routes de test et de santé
Route::get('/ping', function () {
    return response()->json(['message' => 'pong', 'status' => 'success'], 200);
});

Route::get('/health-check', function () {
    return response()->json(['message' => 'API is working', 'status' => 'success'], 200);
});

// Routes d'authentification avec limitation de taux (5 tentatives par minute)
Route::middleware('ip.ratelimit:5,1')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/password/forgot', [UserController::class, 'forgotPassword']);
    Route::post('/password/reset', [UserController::class, 'resetPassword'])->name('password.reset');
});

// Routes de vérification d'email
Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');
Route::get('/email/verify/resend', [UserController::class, 'resendVerificationEmail'])
    ->name('verification.resend')
    ->middleware('auth:sanctum');

// Routes publiques avec cache (5 minutes)
Route::get('/professionals', [ProfessionalController::class, 'index'])->middleware('cache.response:300');
Route::get('/professionals/availability', [ProfessionalController::class, 'indexAvailability'])->middleware('cache.response:300');

// Routes protégées par authentification
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Routes utilisateur
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/user', [UserController::class, 'user']);

    // Routes de profil standardisées
    Route::get('/profile', [ProfileController::class, 'getProfile'])->middleware('cache.response:60'); // Cache de 1 minute
    Route::put('/profile', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/complete', [ProfileController::class, 'completeProfile']);
    Route::get('/profile/completion', [ProfileController::class, 'getCompletionStatus'])->middleware('cache.response:60');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::post('/profile/portfolio', [ProfileController::class, 'uploadPortfolioItem']);
    Route::delete('/profile/portfolio/{id}', [ProfileController::class, 'deletePortfolioItem']);
    Route::put('/profile/availability', [ProfileController::class, 'updateAvailability']);

    // Routes pour les expériences et réalisations
    // Route::apiResource('experiences', ExperienceController::class);

    // Explicit routes for achievements
    // Route::get('/achievements', [AchievementController::class, 'index']);
    // Route::post('/achievements', [AchievementController::class, 'store']);
    // Route::get('/achievements/{achievement}', [AchievementController::class, 'show']);
    // Route::post('/achievements/{achievement}', [AchievementController::class, 'update']);
    // Route::delete('/achievements/{achievement}', [AchievementController::class, 'destroy']);

    // Routes pour les projets liés aux expériences
    Route::post('/experiences/{experience}/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    // Routes pour les contacts
    Route::apiResource('contacts', ContactController::class);

    // Routes pour les offres ouvertes
    // Route::apiResource('open-offers', OpenOfferController::class);
    Route::post('/open-offers/{open_offer}/apply', [OpenOfferController::class, 'apply']);
    Route::get('/open-offers/{open_offer}/applications', [OpenOfferController::class, 'applications']);
    Route::get('/open-offers/{open_offer}/accepted-applications', [OpenOfferController::class, 'acceptedApplications']);
    Route::patch('/offer-applications/{application}/status', [OpenOfferController::class, 'updateApplicationStatus']);
    Route::post('/open-offers/{openOffer}/assign', [OpenOfferController::class, 'assignOfferToProfessional']);
    Route::put('/open-offers/{openOffer}/close', [OpenOfferController::class, 'close']);
    Route::put('/open-offers/{openOffer}/complete', [OpenOfferController::class, 'complete']);
    Route::post('/open-offers/{openOffer}/reject', [OpenOfferController::class, 'rejectOffer']);
    Route::post('/open-offers/{openOffer}/invite', [OpenOfferController::class, 'inviteProfessional']);

    // Routes pour les messages
    Route::get('/open-offers/{openOffer}/messages', [MessageController::class, 'index']);
    Route::post('/open-offers/{openOffer}/messages', [MessageController::class, 'store']);

    // Routes pour les professionnels (authentifiées)
    Route::get('/professionals/{id}', [ProfessionalController::class, 'show']);
    Route::get('/professionals/{id}/offers', [OpenOfferController::class, 'getAttributedOffersForProfessional']);

    // Routes pour les clients
    Route::get('/client/open-offers', [OpenOfferController::class, 'getClientOpenOffers']);
    Route::get('/client/closed-completed-offers', [OpenOfferController::class, 'getClientClosedCompletedOffers']);

    // Routes pour les services
    // Route::apiResource('/service-offers', ServiceOfferController::class);
    Route::get('/service-offers/{id}/public', [ServiceOfferController::class, 'showPublic']);

    // Routes pour le tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'getDashboardData'])->middleware('cache.response:60');

    // Routes pour les projets du tableau de bord
    Route::apiResource('/dashboard/projects', DashboardProjectController::class);
    Route::delete('/dashboard/projects/{project}/attachments/{index}', [DashboardProjectController::class, 'removeAttachment']);
});
