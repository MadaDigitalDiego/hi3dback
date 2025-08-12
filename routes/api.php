<?php

use Illuminate\Http\Request;
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
use App\Http\Controllers\Api\ServiceMessageController;
use App\Http\Controllers\Api\DashboardProjectController;
use App\Http\Controllers\Api\ExplorerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NewProfileController;
use App\Http\Controllers\Api\SubscriptionController;

// Routes de test et de santé
Route::get('/ping', function (Request $request) {
    return response()->json(['message' => 'pong', 'status' => 'success'], 200);
});

Route::get('/health-check', function () {
    return response()->json(['message' => 'API is working', 'status' => 'success'], 200);
});

// Routes d'authentification
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/password/forgot', [UserController::class, 'forgotPassword']);
Route::post('/password/reset', [UserController::class, 'resetPassword'])->name('password.reset');

// Routes de vérification d'email
Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');
Route::get('/email/verify/resend', [UserController::class, 'resendVerificationEmail'])
    ->name('verification.resend')
    ->middleware('auth:sanctum');

// Routes publiques
Route::get('/professionals', [ProfessionalController::class, 'index']);
Route::get('/professionals/filter', [ProfessionalController::class, 'filter']);
Route::get('/professionals/availability', [ProfessionalController::class, 'indexAvailability']);
Route::get('/freelance-profiles', [ProfessionalController::class, 'getAllFreelanceProfiles']);

// Routes pour l'explorateur (publiques)
Route::get('/explorer/professionals', [ExplorerController::class, 'getProfessionals']);
Route::get('/explorer/professionals/{id}', [ExplorerController::class, 'getProfessionalDetails']);
Route::get('/explorer/services', [ExplorerController::class, 'getServices']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/architectural/subcategories', [CategoryController::class, 'getArchitecturalSubcategories']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
// Routes pour les professionnels (authentifiées)
Route::get('/professionals/{id}', [ProfessionalController::class, 'show']);
Route::get('/professionals/{id}/offers', [OpenOfferController::class, 'getAttributedOffersForProfessional']);
Route::get('/professionals/{id}/achievements', [AchievementController::class, 'getByProfessionalId']);
Route::get('/explorer/achievements', [AchievementController::class, 'explorerRealisation']);

Route::get('/professionals/{id}/service-offers', [ServiceOfferController::class, 'getServiceOffersByProfessional']);

// Routes protégées par authentification
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Routes utilisateur
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/user', [UserController::class, 'user']);

    // Routes de profil standardisées
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/complete', [ProfileController::class, 'completeClientProfile']);
    Route::post('/profile/complete-profile', [ProfileController::class, 'completeProfile']);
    Route::get('/profile/completion', [ProfileController::class, 'getCompletionStatus']);
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::post('/profile/cover', [ProfileController::class, 'uploadCover']);
    Route::delete('/delete-profile-avatar', [ProfileController::class, 'deleteAvatar']);
    Route::post('/profile/portfolio', [ProfileController::class, 'uploadPortfolioItem']);
    Route::delete('/profile/portfolio/{id}', [ProfileController::class, 'deletePortfolioItem']);
    Route::put('/profile/availability', [ProfileController::class, 'updateAvailability']);

    // Routes spécifiques pour le profil client
    Route::get('/profile/client', [ProfileController::class, 'getAuthenticatedClientProfile']);
    Route::put('/profile/client', [ProfileController::class, 'updateClientProfile']);
    Route::post('/profile/client', [ProfileController::class, 'createClientProfile']);

    // Nouvelles routes pour la mise à jour du profil client avec JSON et avatar
    Route::post('/profile/client/json', [ProfileController::class, 'updateClientProfileJSON']);
    Route::post('/profile/client/with-avatar', [ProfileController::class, 'updateClientProfileWithAvatar']);

    // Routes pour les expériences et réalisations
    Route::apiResource('experiences', ExperienceController::class);
    // Route::apiResource('achievements', AchievementController::class);
    Route::get('/achievements', [AchievementController::class, 'index']);
    Route::post('/achievements', [AchievementController::class, 'store']);
    Route::get('/achievements/{achievement}', [AchievementController::class, 'show']);
    Route::post('/achievements/{achievement}', [AchievementController::class, 'update']);
    Route::delete('/achievements/{achievement}', [AchievementController::class, 'destroy']);
    Route::get('/achievements/{achievement}/download', [AchievementController::class, 'downloadFile']);

    // Routes pour les projets liés aux expériences
    Route::post('/experiences/{experience}/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    // Routes pour les contacts
    Route::apiResource('contacts', ContactController::class);

    // Routes pour les offres ouvertes
    Route::apiResource('open-offers', OpenOfferController::class);
    Route::post('/open-offers/debug-matching', [OpenOfferController::class, 'debugMatching']);
    Route::post('/open-offers/test-email', [OpenOfferController::class, 'testEmailSending']);
    Route::post('/open-offers/{open_offer}/apply', [OpenOfferController::class, 'apply']);
    Route::get('/open-offers/{open_offer}/applications', [OpenOfferController::class, 'applications']);
    Route::patch('/offer-applications/{application}/status', [OpenOfferController::class, 'updateApplicationStatus']);
    Route::put('/open-offers/{openOffer}/close', [OpenOfferController::class, 'close']);
    Route::put('/open-offers/{openOffer}/complete', [OpenOfferController::class, 'complete']);
    Route::post('/open-offers/{openOffer}/reject', [OpenOfferController::class, 'rejectOffer']);
    Route::post('/open-offers/{openOffer}/invite', [OpenOfferController::class, 'inviteProfessional']);

    // Routes pour les candidatures aux offres
    Route::get('/offer-applications/received', [App\Http\Controllers\Api\OfferApplicationController::class, 'received']);
    Route::put('/offer-applications/{id}/accept', [App\Http\Controllers\Api\OfferApplicationController::class, 'accept']);
    Route::put('/offer-applications/{id}/decline', [App\Http\Controllers\Api\OfferApplicationController::class, 'decline']);

    // Routes pour les messages
    Route::get('/open-offers/{openOffer}/messages', [MessageController::class, 'index']);
    Route::post('/open-offers/{openOffer}/messages', [MessageController::class, 'store']);



    // Routes pour les clients/client/open-offers
    Route::get('/client/open-offers', [OpenOfferController::class, 'getClientOpenOffers']);
    Route::get('/client/open-offers/in-progress', [OpenOfferController::class, 'getClientInProgressOffers']);
    Route::get('/client/open-offers/pending', [OpenOfferController::class, 'getClientPendingOffers']);
    Route::get('/client/open-offers/completed', [OpenOfferController::class, 'getClientClosedOrCompleteOffers']);
    Route::get('/client/closed-completed-offers', [OpenOfferController::class, 'getClientClosedCompletedOffers']);

    // Routes pour les services
    Route::get('/service-offers', [ServiceOfferController::class, 'index']);
    Route::post('/service-offers', [ServiceOfferController::class, 'store']);
    Route::get('/service-offers/{serviceoffers}', [ServiceOfferController::class, 'show']);
    Route::delete('/service-offers/{serviceoffers}', [ServiceOfferController::class, 'destroy']);
    Route::post('/service-offers/{serviceoffers}', [ServiceOfferController::class, 'update']);
    // Route::apiResource('/service-offers', ServiceOfferController::class);
    Route::get('/service-offers/filter', [ServiceOfferController::class, 'filter']);
    Route::get('/service-offers/{id}/public', [ServiceOfferController::class, 'showPublic']);


    Route::get('/service-offers/{serviceOffer}/download', [ServiceOfferController::class, 'downloadFile']);

    // Routes pour les messages de service
    Route::post('/messages/send', [ServiceMessageController::class, 'send']);
    Route::get('/messages', [ServiceMessageController::class, 'index']);
    Route::get('/messages/service/{serviceId}', [ServiceMessageController::class, 'getServiceMessages']);
    Route::put('/messages/{id}/read', [ServiceMessageController::class, 'markAsRead']);
    Route::get('/messages/conversation/{userId}', [ServiceMessageController::class, 'getConversation']);

    // Routes pour le tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);

    Route::get('/activities', [DashboardController::class, 'getAllACtivity']);

    // Routes pour les projets du tableau de bord
    Route::apiResource('/dashboard/projects', DashboardProjectController::class);
    Route::get('/dashboard/projects/filter', [DashboardProjectController::class, 'filter']);
    Route::delete('/dashboard/projects/{project}/attachments/{index}', [DashboardProjectController::class, 'removeAttachment']);

    // Nouvelles routes pour la structure de profil unifiée
    Route::get('/profile/new', [NewProfileController::class, 'getProfile']);
    Route::put('/profile/new', [NewProfileController::class, 'updateProfile']);
    Route::post('/profile/new/complete', [NewProfileController::class, 'completeProfile']);
    Route::get('/profile/new/completion', [NewProfileController::class, 'getCompletionStatus']);
    Route::post('/profile/new/avatar', [NewProfileController::class, 'uploadAvatar']);
    Route::post('/profile/new/portfolio', [NewProfileController::class, 'uploadPortfolioItem']);
    Route::delete('/profile/new/portfolio/{id}', [NewProfileController::class, 'deletePortfolioItem']);
    Route::put('/profile/new/availability', [NewProfileController::class, 'updateAvailability']);

    // Routes pour les abonnements
    Route::post('/subscriptions', [SubscriptionController::class, 'createSubscription']);
    Route::post('/subscriptions/confirm', [SubscriptionController::class, 'confirmPayment']);
});
