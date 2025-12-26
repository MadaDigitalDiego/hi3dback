<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\OpenOffer;
use Illuminate\Http\Request;
use App\Models\OfferApplication;
use Illuminate\Http\JsonResponse; // Import ProfessionalProfile model
use App\Models\ProfessionalProfile; // Use the new Request class
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreOpenOfferRequest;
use Illuminate\Support\Facades\Notification; // Import the notification class
use App\Notifications\NewOpenOfferNotification;
use App\Notifications\DirectOfferInvitationNotification;
use App\Notifications\NewApplicationNotification;
use App\Notifications\ApplicationStatusChangedNotification;
use App\Notifications\OfferAssignedNotification;
use App\Notifications\OfferClosedNotification;
use App\Notifications\OfferCompletedNotification;
use App\Notifications\OfferReactivatedWithProfessionalNotification;
use App\Notifications\OfferReopenedToAllNotification;
use App\Notifications\InvitationDeclinedNotification;
use App\Services\OfferMatchingService;


class OpenOfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            // Modify the query to exclude 'closed' and 'completed' offers
            $openOffers = OpenOffer::with('user')
                ->whereNotIn('status', ['closed', 'completed'])
                ->get();
            return response()->json(['open_offers' => $openOffers]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la liste des offres ouvertes: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des offres ouvertes.'], 500);
        }
    }


    /**
     * Store a newly created resource in storage with dynamic filters.
     */
    public function store(StoreOpenOfferRequest $request)
    {
        $validatedData = $request->validated();

        $openOffer = new OpenOffer();
        $openOffer->user_id = $request->user()->id;
        $openOffer->title = $validatedData['title'];
        $openOffer->categories = $validatedData['categories'] ?? null;
        $openOffer->budget = $validatedData['budget'] ?? null;
        $openOffer->deadline = $validatedData['deadline'] ?? null;
        $openOffer->company = $validatedData['company'] ?? null;
        $openOffer->website = $validatedData['website'] ?? null;
        $openOffer->description = $validatedData['description'];
        $openOffer->status = 'open';

        // Traitement des filtres
        $openOffer->filters = isset($validatedData['filters'])
            ? json_encode($validatedData['filters'])
            : null;

        // Gestion des fichiers
        $filePaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    $path = $file->store('offer_files', 'public');
                    $filePaths[] = ['path' => $path, 'original_name' => $file->getClientOriginalName()];
                } catch (\Exception $e) {
                    Log::error('File upload error: ' . $e->getMessage());
                    return response()->json(['message' => 'File upload error'], 500);
                }
            }
            $openOffer->files = json_encode($filePaths);
        }

        // Liens d'attachements externes (interface "Brief")
        if (isset($validatedData['attachment_links']) && is_array($validatedData['attachment_links'])) {
            $openOffer->attachment_links = json_encode($validatedData['attachment_links']);
        }

        $openOffer->save();

        // Appel du service de matching après la création de l'offre
        try {
            $matchingService = new OfferMatchingService();
            $matchedCount = $matchingService->matchAndNotify($openOffer);

            Log::info("Matched {$matchedCount} profiles for offer #{$openOffer->id}");
        } catch (\Exception $e) {
            Log::error('Matching error: ' . $e->getMessage());
            // Ne pas retourner d'erreur car l'offre a bien été créée
        }

        return response()->json([
            'open_offer' => $openOffer,
            'message' => 'Offre créée avec succès. Correspondances trouvées: ' . ($matchedCount ?? 0),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OpenOffer $openOffer): JsonResponse
    {
        try {
            $openOffer->increment('views_count');
            return response()->json(['open_offer' => $openOffer->load('user.clientProfile', 'applications.freelanceProfile.user')]); // Keep the relation name for now as it's defined in the OfferApplication model
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de l\'offre ouverte ID ' . $openOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération de l\'offre ouverte.'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OpenOffer $openOffer): JsonResponse
    {
        if ($openOffer->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:255',
            'budget' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:2048',
            'attachment_links' => 'nullable|array',
            'attachment_links.*' => 'url|max:2048',
            'recruitment_type' => 'in:company,personal',
            'open_to_applications' => 'boolean',
            'auto_invite' => 'boolean',
            // 'status' => 'in:open,closed,in_progress,completed,pending', // Ne pas permettre de changer le statut directement via update
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $validatedData = $validator->validated();

            // Gestion des fichiers pour la mise à jour (similaire à la création)
            $filePaths = [];
            if ($request->hasFile('files')) {
                // Optionnel: Supprimer les anciens fichiers si nécessaire avant d'uploader les nouveaux
                // Storage::disk('public')->delete($openOffer->files); // Exemple de suppression (adapter selon la structure de stockage)

                foreach ($request->file('files') as $file) {
                    try {
                        $path = $file->store('offer_files', 'public');
                        $filePaths[] = ['path' => $path, 'original_name' => $file->getClientOriginalName()];
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de l\'upload du fichier (mise à jour): ' . $e->getMessage());
                        return response()->json(['message' => 'Erreur lors de l\'upload d\'un fichier pendant la mise à jour.'], 500);
                    }
                }
                $validatedData['files'] = json_encode($filePaths);
            }

            // Normaliser les liens d'attachements en JSON
            if (array_key_exists('attachment_links', $validatedData)) {
                $validatedData['attachment_links'] = $validatedData['attachment_links'] !== null
                    ? json_encode($validatedData['attachment_links'])
                    : null;
            }

            // Stocker les compétences dans la colonne categories au lieu des catégories
            if (isset($validatedData['filters']) && isset($validatedData['filters']['skills'])) {
                $validatedData['categories'] = $validatedData['filters']['skills'];
            }

            $openOffer->update($validatedData);
            return response()->json(['open_offer' => $openOffer, 'message' => 'Offre ouverte mise à jour avec succès.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'offre ouverte ID ' . $openOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour de l\'offre ouverte.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OpenOffer $openOffer): JsonResponse
    {
        if ($openOffer->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }
        // Optionnel: Supprimer les fichiers associés à l'offre avant de supprimer l'offre elle-même
        // Storage::disk('public')->delete($openOffer->files); // Adapter selon la structure de stockage
        try {
            $openOffer->delete();
            return response()->json(['message' => 'Offre ouverte supprimée avec succès.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'offre ouverte ID ' . $openOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression de l\'offre ouverte.'], 500);
        }
    }

    /**
     * Apply to an open offer or respond to an invitation.
     */
    public function apply(Request $request, OpenOffer $openOffer): JsonResponse
    {
        if ($openOffer->status !== 'open') {
            return response()->json(['message' => 'This offer is no longer open for applications.'], 400);
        }

        $validator = Validator::make($request->all(), ['proposal' => 'nullable|string']);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
	        if (!$user->is_professional || !$user->professionalProfile) {
            return response()->json(['message' => 'Seuls les professionnels avec un profil professionnel peuvent postuler.'], 403);
        }

		        // Check subscription/application limits for this professional
		        if (!$user->canPerformAction('applications')) {
		            // Autoriser la réponse à une invitation pour cette offre même si la limite est atteinte
		            $hasInvitationForThisOffer = OfferApplication::where('open_offer_id', $openOffer->id)
		                ->where('professional_profile_id', $user->professionalProfile->id)
		                ->where('status', 'invited')
		                ->exists();

		            if (!$hasInvitationForThisOffer) {
		                $subscription = $user->currentSubscription();
		                $message = $subscription
	                ? 'Vous avez atteint la limite de candidatures pour votre abonnement. Veuillez mettre  e0 niveau votre plan.'
	                : 'Plan Free actif. Un abonnement est requis pour acc e9der  e0 toutes les fonctionnalit e9s.';

		                return response()->json(['message' => $message], 403);
		            }
		        }

        try {
            $existingInvitedApplication = OfferApplication::where('open_offer_id', $openOffer->id)
                ->where('professional_profile_id', $user->professionalProfile->id)
                ->where('status', 'invited')
                ->first(); // Use first() to get the application if it exists

            if ($existingInvitedApplication) {
                // Professional is responding to an invitation, update the existing application
                try {
                    $existingInvitedApplication->status = 'pending'; // Or 'applied', or 'accepted' based on your workflow
                    $existingInvitedApplication->proposal = $validator->validated()['proposal'] ?? $existingInvitedApplication->proposal; // Keep existing proposal or update if provided
                    $existingInvitedApplication->save();

                    // Notify the client that an invited professional has submitted an application
                    if ($openOffer->user) {
                        Notification::send($openOffer->user, new NewApplicationNotification($existingInvitedApplication));
                    }

                    return response()->json(['application' => $existingInvitedApplication, 'message' => 'Invitation acceptée et candidature soumise avec succès.'], 200);
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la mise à jour de la candidature invitée: ' . $e->getMessage());
                    return response()->json(['message' => 'Erreur serveur lors de la mise à jour de la candidature. Veuillez réessayer plus tard.'], 500);
                }
            } else {
                // Professional is applying normally, check for existing applications (excluding invited)
                $existingApplication = OfferApplication::where('open_offer_id', $openOffer->id)
                    ->where('professional_profile_id', $user->professionalProfile->id)
                    ->whereNotIn('status', ['invited'])
                    ->exists();

                if ($existingApplication) {
                    return response()->json(['message' => 'Vous avez déjà postulé à cette offre.'], 409);
                }

                // Create a new application
                try {
                    $application = OfferApplication::create([
                        'open_offer_id' => $openOffer->id,
                        'professional_profile_id' => $user->professionalProfile->id,
                        'proposal' => $validator->validated()['proposal'] ?? null,
                        'status' => 'pending',
                    ]);

                    // Notify the client that a new application has been received
                    if ($openOffer->user) {
                        Notification::send($openOffer->user, new NewApplicationNotification($application));
                    }

                    return response()->json(['application' => $application, 'message' => 'Candidature soumise avec succès.'], 201);
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la soumission de la candidature: ' . $e->getMessage());
                    return response()->json(['message' => 'Erreur serveur lors de la soumission de la candidature. Veuillez réessayer plus tard.'], 500);
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du processus de candidature à l\'offre ouverte ID ' . $openOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la soumission de la candidature.'], 500);
        }
    }

    /**
     * List applications for a specific open offer (for the offer creator).
     */
    public function applications(OpenOffer $openOffer): JsonResponse
    {
        if ($openOffer->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé à voir les candidatures.'], 403);
        }
        try {
            $applications = $openOffer->applications()->with('freelanceProfile.user')->get(); // Keep the relation name for now as it's defined in the OfferApplication model
            return response()->json(['applications' => $applications]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des candidatures pour l\'offre ouverte ID ' . $openOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des candidatures.'], 500);
        }
    }

    /**
     * List accepted applications for a specific open offer (for assignment selection).
     */
    public function acceptedApplications(OpenOffer $openOffer): JsonResponse
    {
        if ($openOffer->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé à voir les candidatures.'], 403);
        }

        try {
            $acceptedApplications = $openOffer->applications()
                ->where('status', 'accepted')
                ->with('freelanceProfile.user')
                ->get();

            return response()->json(['accepted_applications' => $acceptedApplications]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des candidatures acceptées pour l\'offre ouverte ID ' . $openOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des candidatures acceptées.'], 500);
        }
    }

    /**
     * Accept or reject an application (without affecting the offer status).
     */
    public function updateApplicationStatus(Request $request, OfferApplication $application): JsonResponse
    {
        $openOffer = $application->openOffer;
        if ($openOffer->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé à modifier le statut de la candidature.'], 403);
        }

        if ($openOffer->status !== 'open') {
            return response()->json(['message' => 'Le statut de l\'offre doit être "open" pour modifier les candidatures.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:accepted,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $validatedData = $validator->validated();
            $application->update($validatedData);

            // Notify the professional about the change of status of their application
            $professionalUser = $application->freelanceProfile ? $application->freelanceProfile->user : null;
            if ($professionalUser) {
                Notification::send($professionalUser, new ApplicationStatusChangedNotification($application));
            }

            // if ($validatedData['status'] === 'accepted') {
            //     $openOffer->status = 'in_progress';
            //     $openOffer->save();
            // }

            return response()->json(['application' => $application, 'open_offer_status' => $openOffer->status, 'message' => 'Statut de la candidature mis à jour et statut de l\'offre mis à jour.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du statut de la candidature ID ' . $application->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour du statut de la candidature.'], 500);
        }
    }

    /**
     * Assign the offer to a chosen professional and transition offer status to in_progress.
     */
    public function assignOfferToProfessional(Request $request, OpenOffer $openOffer): JsonResponse
    {
        if ($openOffer->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé à attribuer cette offre.'], 403);
        }

        if ($openOffer->status !== 'open') {
            return response()->json(['message' => 'L\'offre doit être en statut "open" pour être attribuée.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:offer_applications,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Vérifier que la candidature appartient bien à cette offre
            $chosenApplication = OfferApplication::where('id', $request->application_id)
                ->where('open_offer_id', $openOffer->id)
                ->first();

            if (!$chosenApplication) {
                return response()->json(['message' => 'La candidature spécifiée n\'appartient pas à cette offre.'], 400);
            }

            // Vérifier que la candidature est acceptée
            if ($chosenApplication->status !== 'accepted') {
                return response()->json(['message' => 'Seules les candidatures acceptées peuvent être attribuées.'], 400);
            }

            // Passer l'offre en statut "in_progress"
            $openOffer->status = 'in_progress';
            $openOffer->save();

            // Enregistrer l'attribution dans la table open_offer_user
            $professionalUserId = $chosenApplication->freelanceProfile->user_id;
            $openOffer->professionals()->syncWithoutDetaching([$professionalUserId]);

            // Notify the chosen professional that the offer has been assigned to them
            $professionalUser = $chosenApplication->freelanceProfile ? $chosenApplication->freelanceProfile->user : null;
            if ($professionalUser) {
                Notification::send($professionalUser, new OfferAssignedNotification($openOffer, $chosenApplication));
            }


            // Rejeter automatiquement toutes les autres candidatures acceptées
            OfferApplication::where('open_offer_id', $openOffer->id)
                ->where('id', '!=', $chosenApplication->id)
                ->whereIn('status', ['accepted', 'pending', 'invited'])
                ->update(['status' => 'rejected']);

            // Recharger l'offre avec ses relations
            $openOffer->load(['applications.freelanceProfile.user']);

            return response()->json([
                'open_offer' => $openOffer,
                'assigned_application' => $chosenApplication,
                'message' => 'Offre attribuée avec succès au professionnel choisi.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'attribution de l\'offre ID ' . $openOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'attribution de l\'offre.'], 500);
        }
    }

    /**
     * Close the specified open offer. Can be closed from 'open' or 'in_progress' status.
     */
    public function close(OpenOffer $openOffer): JsonResponse
    {
        if ($openOffer->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé à clôturer cette offre.'], 403);
        }

        if ($openOffer->status === 'closed' || $openOffer->status === 'completed') {
            return response()->json(['message' => 'Cette offre est déjà clôturée ou complétée.'], 400);
        }

        try {
            $openOffer->status = 'closed';
            $openOffer->save();

            // Notify all professionals related to this offer that it has been closed
            $applications = $openOffer->applications()->with('freelanceProfile.user')->get();

            $professionalUsers = $applications
                ->map(function ($application) {
                    return $application->freelanceProfile ? $application->freelanceProfile->user : null;
                })
                ->filter()
                ->unique('id');

            foreach ($professionalUsers as $professionalUser) {
                Notification::send($professionalUser, new OfferClosedNotification($openOffer));
            }

            return response()->json(['open_offer' => $openOffer, 'message' => 'Offre ouverte clôturée avec succès.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la clôture de l\'offre ouverte ID ' . $openOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la clôture de l\'offre ouverte.'], 500);
        }
    }

    /**
     * Mark the specified open offer as completed. Must be in 'in_progress' status.
     */
    public function complete(OpenOffer $openOffer): JsonResponse
    {
        if ($openOffer->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé à compléter cette offre.'], 403);
        }

        if ($openOffer->status !== 'in_progress') {
            return response()->json(['message' => 'L\'offre doit être en statut "in_progress" pour être marquée comme complétée.'], 400);
        }

        try {
            $openOffer->status = 'completed';
            $openOffer->save();

            // Notify assigned professionals that the offer has been marked as completed
            $assignedProfessionals = $openOffer->professionals()->get();

            foreach ($assignedProfessionals as $professionalUser) {
                Notification::send($professionalUser, new OfferCompletedNotification($openOffer));
            }

            return response()->json(['open_offer' => $openOffer, 'message' => 'Offre ouverte marquée comme complétée avec succès.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage comme complété de l\'offre ouverte ID ' . $openOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du marquage comme complété de l\'offre ouverte.'], 500);
        }
    }

    /**
     * Reactivate a completed or closed open offer.
     *
     * Modes supportés :
     * - continue_with_professional : l'offre revient en "in_progress" avec les professionnels déjà attribués,
     *   les nouvelles candidatures sont désactivées.
     * - reopen_to_all : l'offre revient en "open", toutes les candidatures deviennent "rejected"
     *   et les professionnels attribués sont détachés.
     *
     * Si le mode continue_with_professional est demandé mais qu'aucun professionnel n'est attribué,
     * alors l'offre est automatiquement rouverte à tous (reopen_to_all).
     */
    public function reactivate(Request $request, OpenOffer $openOffer): JsonResponse
    {
        if ($openOffer->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé à réactiver cette offre.'], 403);
        }

        if (!in_array($openOffer->status, ['completed', 'closed'])) {
            return response()->json([
                'message' => 'Seules les offres complétées ou fermées peuvent être réactivées.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'mode' => 'required|in:continue_with_professional,reopen_to_all',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $mode = $validator->validated()['mode'];

        try {
            // Récupérer les professionnels actuellement attribués (s'il y en a)
            $assignedProfessionals = $openOffer->professionals()->get();
            $hasAssignedProfessionals = $assignedProfessionals->isNotEmpty();

            // Cas 1 : continuer avec le ou les professionnels déjà attribués
            if ($mode === 'continue_with_professional' && $hasAssignedProfessionals) {
                $openOffer->status = 'in_progress';
                $openOffer->open_to_applications = false;
                $openOffer->save();

                // Notifier les professionnels que la mission est réactivée avec eux
                foreach ($assignedProfessionals as $professionalUser) {
                    Notification::send($professionalUser, new OfferReactivatedWithProfessionalNotification($openOffer));
                }

                $openOffer->refresh();

                return response()->json([
                    'open_offer' => $openOffer,
                    'mode' => 'continue_with_professional',
                    'message' => 'Offre réactivée avec les professionnels attribués.',
                ]);
            }

            // Cas 2 : réouverture à tous (mode explicite ou fallback si aucun pro attribué)
            $openOffer->status = 'open';
            $openOffer->open_to_applications = true;

            // Détacher les professionnels attribués (s'il y en a)
            if ($hasAssignedProfessionals) {
                $openOffer->professionals()->detach();
            }

            // Toutes les candidatures de cette offre repassent en "rejected"
            OfferApplication::where('open_offer_id', $openOffer->id)
                ->update(['status' => 'rejected']);

            $openOffer->save();

            // Notifier les anciens professionnels attribués que l'offre est rouverte à tous
            foreach ($assignedProfessionals as $professionalUser) {
                Notification::send($professionalUser, new OfferReopenedToAllNotification($openOffer));
            }

            $openOffer->refresh();

            return response()->json([
                'open_offer' => $openOffer,
                'mode' => 'reopen_to_all',
                'message' => 'Offre réactivée et rouverte à tous les professionnels.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la réactivation de l\'offre ouverte ID ' . $openOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la réactivation de l\'offre ouverte.'], 500);
        }
    }

    /**
     * Invite a professional directly to an open offer.
     */
    public function inviteProfessional(Request $request, OpenOffer $openOffer): JsonResponse
    {
        if ($openOffer->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé à inviter des professionnels pour cette offre.'], 403);
        }

	    	if ($openOffer->status !== 'open' && $openOffer->status !== 'pending') { // Allow invitation for 'pending' and 'open' offers
	    	    return response()->json(['message' => 'Les invitations ne peuvent être envoyées que pour les offres en statut "pending" ou "open".'], 400);
	    	}
	    	
	    	$user = $request->user();
	    	
	    	// Check subscription/invitation limits for this client
	    	if (!$user->canPerformAction('applications')) {
	    	    $subscription = $user->currentSubscription();
	    	    $message = $subscription
	    	        ? 'Vous avez atteint la limite de candidatures ou d\'invitations pour votre abonnement. Veuillez mettre à niveau votre plan.'
	    	        : 'Plan Free actif. Un abonnement est requis pour accéder à toutes les fonctionnalités.';
	    	
	    	    return response()->json(['message' => $message], 403);
	    	}
	    	
	    	$validator = Validator::make($request->all(), [
            'professional_id' => 'required|exists:users,id,is_professional,1', // Ensure it's a professional user
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $professionalUser = User::find($request->professional_id);
        if (!$professionalUser->professionalProfile) {
            return response()->json(['message' => 'Le professionnel invité n\'a pas de profil professionnel.'], 422);
        }

        // try {
            // Check if already invited or applied
            $existingInvitation = OfferApplication::where('open_offer_id', $openOffer->id)
                ->where('professional_profile_id', $professionalUser->professionalProfile->id)
                ->whereIn('status', ['pending', 'accepted', 'invited']) // Check for existing pending, accepted or invited applications/invitations
                ->exists();

            if ($existingInvitation) {
                return response()->json(['message' => 'Ce professionnel a déjà été invité ou a déjà postulé à cette offre.'], 409);
            }

            // Create invitation as an OfferApplication with 'invited' status
            $invitation = OfferApplication::create([
                'open_offer_id' => $openOffer->id,
                'professional_profile_id' => $professionalUser->professionalProfile->id,
                'status' => 'invited', // Status to indicate it's an invitation
                'proposal' => '',
            ]);
            // Send notification to the invited professional
            // Notification::send($professionalUser, new DirectOfferInvitationNotification($openOffer, auth()->user())); // Original line - sending to User, should be fine here if $professionalUser is indeed a User model.

            // No change needed here if $professionalUser is already a User model.
            // However, double check that $professionalUser is of type User and not ProfessionalProfile.
            // In your code, you are using User::find($request->professional_id), which is correct to get a User model.
            Notification::send($professionalUser, new DirectOfferInvitationNotification($openOffer, auth()->user()));

            // Send notification to the invited professional
            //Notification::send($professionalUser, new DirectOfferInvitationNotification($openOffer, auth()->user())); // Pass the client user as well

            return response()->json([
                'invitation' => $invitation,
                'message' => 'Professionnel invité avec succès à l\'offre.',
            ], 201);
        // } catch (\Exception $e) {
        //     Log::error('Erreur lors de l\'invitation du professionnel ID ' . $request->professional_id . ' à l\'offre ouverte ID ' . $openOffer->id . ': ' . $e->getMessage());
        //     return response()->json(['message' => 'Erreur lors de l\'invitation du professionnel.'], 500);
        // }
    }


    /**
     * Get attributed open offers for a specific professional.
     *
     * @param  int  $professionalId
     * @return JsonResponse
     */
    public function getAttributedOffersForProfessional(int $professionalId): JsonResponse
    {
        $professionalUser = User::find($professionalId);

        if (!$professionalUser || !$professionalUser->is_professional) {
            return response()->json(['message' => 'Professional not found.'], 404);
        }

        try {
            // Load the attributedOpenOffers relationship
            $attributedOffers = $professionalUser->attributedOpenOffers()->with('user')->get();

            return response()->json(['attributed_open_offers' => $attributedOffers]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des offres attribuées pour le professionnel ID ' . $professionalId . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des offres attribuées.'], 500);
        }
    }

    /**
     * Reject an application for an open offer by a professional.
     *
     * @param  Request  $request
     * @param  OpenOffer $openOffer
     * @return JsonResponse
     */
    public function rejectOffer(Request $request, OpenOffer $openOffer): JsonResponse
    {
        $user = $request->user();

        if (!$user->is_professional || !$user->professionalProfile) {
            return response()->json(['message' => 'Seuls les professionnels peuvent refuser une offre.'], 403);
        }

        try {
            // Check if an application exists for this professional and offer
            $application = OfferApplication::where('open_offer_id', $openOffer->id)
                ->where('professional_profile_id', $user->professionalProfile->id)
                ->first();

            if (!$application) {
                return response()->json(['message' => 'Vous n\'avez pas postulé à cette offre ou vous n\'avez pas été invité.'], 404);
            }

            // Check if the application is already accepted or rejected by client
            if ($application->status === 'accepted' || $application->status === 'rejected') {
                return response()->json(['message' => 'Vous ne pouvez pas refuser une candidature déjà acceptée ou rejetée par le client.'], 400);
            }
            // Check if the application is already rejected by professional
            if ($application->status === 'rejected') {
                return response()->json(['message' => 'Vous avez déjà refusé cette offre.'], 409);
            }

            $application->status = 'rejected';
            $application->save();

            // Notify the client that the invited professional has declined the offer
            if ($openOffer->user) {
                Notification::send($openOffer->user, new InvitationDeclinedNotification($application));
            }

            return response()->json(['application' => $application, 'message' => 'Offre refusée avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du refus de l\'offre ouverte ID ' . $openOffer->id . ' par le professionnel ID ' . $user->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du refus de l\'offre.'], 500);
        }
    }

    /**
     * Get all open offers for the authenticated client.
     *
     * @return JsonResponse
     */
    public function getClientOpenOffers(): JsonResponse
    {
        $client = auth()->user();

        if (!$client) {
            return response()->json(['message' => 'Client non authentifié.'], 401); // Or handle unauthenticated user appropriately
        }

        if ($client->is_professional) {
            return response()->json(['message' => 'Les professionnels n\'ont pas accès à cette fonctionnalité.'], 403); // Or appropriate message
        }

        try {
            $openOffers = OpenOffer::with('applications', 'applications.freelanceProfile.user') // Keep the relation name for now as it's defined in the OfferApplication model
                ->where('user_id', $client->id)
                ->whereNotIn('status', ['closed', 'completed']) // Exclude closed and completed offers
                ->latest()
                ->get();

            return response()->json(['client_open_offers' => $openOffers]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des offres ouvertes du client ID ' . $client->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des offres ouvertes du client.'], 500);
        }
    }

    public function getClientInProgressOffers(): JsonResponse
    {
        $client = auth()->user();

        if (!$client) {
            return response()->json(['message' => 'Client non authentifié.'], 401); // Or handle unauthenticated user appropriately
        }

        if ($client->is_professional) {
            return response()->json(['message' => 'Les professionnels n\'ont pas accès à cette fonctionnalité.'], 403); // Or appropriate message
        }

        try {
            $openOffers = OpenOffer::with('applications', 'applications.freelanceProfile.user') // Keep the relation name for now as it's defined in the OfferApplication model
                ->where('user_id', $client->id)
                ->whereNotIn('status', ['pending', 'open', 'closed', 'completed']) // Exclude closed and completed offers
                ->latest()
                ->get();

            return response()->json(['offers' => $openOffers]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des offres ouvertes du client ID ' . $client->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des offres ouvertes du client.'], 500);
        }
    }

    public function getClientPendingOffers(): JsonResponse
    {
        $client = auth()->user();

        if (!$client) {
            return response()->json(['message' => 'Client non authentifié.'], 401); // Or handle unauthenticated user appropriately
        }

        if ($client->is_professional) {
            return response()->json(['message' => 'Les professionnels n\'ont pas accès à cette fonctionnalité.'], 403); // Or appropriate message
        }

        try {
            $openOffers = OpenOffer::with('applications', 'applications.freelanceProfile.user') // Keep the relation name for now as it's defined in the OfferApplication model
                ->where('user_id', $client->id)
                ->whereNotIn('status', ['in_progress', 'closed', 'completed']) // Exclude closed and completed offers
                ->latest()
                ->get();

            return response()->json(['offers' => $openOffers]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des offres ouvertes du client ID ' . $client->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des offres ouvertes du client.'], 500);
        }
    }

    public function getClientClosedOrCompleteOffers(): JsonResponse
    {
        $client = auth()->user();

        if (!$client) {
            return response()->json(['message' => 'Client non authentifié.'], 401); // Or handle unauthenticated user appropriately
        }
        if ($client->is_professional) {
            return response()->json(['message' => 'Les professionnels n\'ont pas accès à cette fonctionnalité.'], 403); // Or appropriate message
        }

        try {
            $closedCompletedOffers = OpenOffer::with('applications', 'applications.freelanceProfile.user') // Keep the relation name for now as it's defined in the OfferApplication model
                ->where('user_id', $client->id)
                ->whereIn('status', ['closed', 'completed']) // Include only closed and completed offers
                ->latest()
                ->get();

            return response()->json(['offers' => $closedCompletedOffers]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des offres clôturées/complétées du client ID ' . $client->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des offres clôturées/complétées du client.'], 500);
        }
    }

    /**
     * Get all closed or completed offers for the authenticated client.
     *
     * @return JsonResponse
     */
    public function getClientClosedCompletedOffers(): JsonResponse
    {
        $client = auth()->user();

        if (!$client) {
            return response()->json(['message' => 'Client non authentifié.'], 401); // Or handle unauthenticated user appropriately
        }
        if ($client->is_professional) {
            return response()->json(['message' => 'Les professionnels n\'ont pas accès à cette fonctionnalité.'], 403); // Or appropriate message
        }

        try {
            $closedCompletedOffers = OpenOffer::with('applications', 'applications.freelanceProfile.user') // Keep the relation name for now as it's defined in the OfferApplication model
                ->where('user_id', $client->id)
                ->whereIn('status', ['closed', 'completed']) // Include only closed and completed offers
                ->latest()
                ->get();

            return response()->json(['client_closed_completed_offers' => $closedCompletedOffers]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des offres clôturées/complétées du client ID ' . $client->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des offres clôturées/complétées du client.'], 500);
        }
    }

    /**
     * Test email sending to professionals
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function testEmailSending(Request $request): JsonResponse
    {
        try {
            $testEmail = $request->input('test_email');
            $sendToAll = $request->input('send_to_all', false);

            $debugInfo = [
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name'),
                ],
                'test_results' => []
            ];

            // Créer une offre de test
            $testOffer = new OpenOffer([
                'title' => 'Test Offer - Email Diagnostic',
                'description' => 'Ceci est une offre de test pour diagnostiquer l\'envoi d\'emails',
                'budget' => '1000€',
                'company' => 'Test Company',
                'user_id' => auth()->id()
            ]);

            if ($testEmail) {
                // Test avec un email spécifique
                $testUser = User::where('email', $testEmail)->first();
                if (!$testUser) {
                    return response()->json([
                        'error' => 'Utilisateur avec cet email non trouvé',
                        'debug_info' => $debugInfo
                    ], 404);
                }

                Log::info('Test envoi email à: ' . $testEmail);
                Notification::send($testUser, new NewOpenOfferNotification($testOffer));

                $debugInfo['test_results'][] = [
                    'email' => $testEmail,
                    'user_id' => $testUser->id,
                    'status' => 'sent',
                    'is_professional' => $testUser->is_professional
                ];
            } elseif ($sendToAll) {
                // Test avec tous les professionnels
                $professionals = User::where('is_professional', true)
                    ->whereNotNull('email')
                    ->limit(5) // Limiter à 5 pour éviter le spam
                    ->get();

                Log::info('Test envoi email à ' . $professionals->count() . ' professionnels');

                foreach ($professionals as $professional) {
                    try {
                        Notification::send($professional, new NewOpenOfferNotification($testOffer));
                        $debugInfo['test_results'][] = [
                            'email' => $professional->email,
                            'user_id' => $professional->id,
                            'status' => 'sent',
                            'name' => $professional->first_name . ' ' . $professional->last_name
                        ];
                    } catch (\Exception $e) {
                        $debugInfo['test_results'][] = [
                            'email' => $professional->email,
                            'user_id' => $professional->id,
                            'status' => 'error',
                            'error' => $e->getMessage()
                        ];
                    }
                }
            } else {
                // Test avec le premier professionnel trouvé
                $testUser = User::where('is_professional', true)
                    ->whereNotNull('email')
                    ->first();

                if (!$testUser) {
                    return response()->json([
                        'error' => 'Aucun utilisateur professionnel avec email trouvé',
                        'debug_info' => $debugInfo
                    ], 404);
                }

                Log::info('Test envoi email à: ' . $testUser->email);
                Notification::send($testUser, new NewOpenOfferNotification($testOffer));

                $debugInfo['test_results'][] = [
                    'email' => $testUser->email,
                    'user_id' => $testUser->id,
                    'status' => 'sent',
                    'name' => $testUser->first_name . ' ' . $testUser->last_name
                ];
            }

            return response()->json([
                'message' => 'Test d\'envoi d\'email terminé',
                'debug_info' => $debugInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du test d\'envoi d\'email: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors du test d\'envoi d\'email'
            ], 500);
        }
    }

    /**
     * Debug method to test professional matching
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function debugMatching(Request $request): JsonResponse
    {
        try {
            $filters = $request->input('filters', []);

            Log::info('Debug matching avec filtres: ' . json_encode($filters));

            $query = ProfessionalProfile::query()
                ->with('user')
                ->whereHas('user', function ($q) {
                    $q->where('is_professional', true);
                });

            // Compter le nombre total de professionnels avant filtrage
            $totalProfessionals = ProfessionalProfile::whereHas('user', function ($q) {
                $q->where('is_professional', true);
            })->count();

            $debugInfo = [
                'total_professionals' => $totalProfessionals,
                'filters_applied' => [],
                'sql_queries' => [],
                'results' => []
            ];

            if (!empty($filters)) {
                if (isset($filters['languages']) && is_array($filters['languages']) && !empty($filters['languages'])) {
                    $query->where(function ($q) use ($filters) {
                        foreach ($filters['languages'] as $lang) {
                            if (!empty($lang)) {
                                $q->orWhereJsonContains('languages', $lang)
                                    ->orWhereRaw("JSON_SEARCH(languages, 'one', ?) IS NOT NULL", [$lang]);
                            }
                        }
                    });
                    $debugInfo['filters_applied'][] = 'languages: ' . json_encode($filters['languages']);
                }

                if (isset($filters['skills']) && is_array($filters['skills']) && !empty($filters['skills'])) {
                    $query->where(function ($q) use ($filters) {
                        foreach ($filters['skills'] as $skill) {
                            if (!empty($skill)) {
                                $q->orWhereJsonContains('skills', $skill)
                                    ->orWhereRaw("JSON_SEARCH(skills, 'one', ?) IS NOT NULL", [$skill]);
                            }
                        }
                    });
                    $debugInfo['filters_applied'][] = 'skills: ' . json_encode($filters['skills']);
                }

                if (isset($filters['location']) && !empty($filters['location'])) {
                    $query->where('city', 'like', '%' . $filters['location'] . '%');
                    $debugInfo['filters_applied'][] = 'location: ' . $filters['location'];
                }

                if (isset($filters['experience_years']) && is_numeric($filters['experience_years'])) {
                    $query->where('years_of_experience', '>=', $filters['experience_years']);
                    $debugInfo['filters_applied'][] = 'experience_years: >= ' . $filters['experience_years'];
                }

                if (isset($filters['availability_status']) && !empty($filters['availability_status'])) {
                    $query->where('availability_status', $filters['availability_status']);
                    $debugInfo['filters_applied'][] = 'availability_status: ' . $filters['availability_status'];
                }
            }

            $eligibleProfessionals = $query->get();
            $eligibleProfessionalsCount = $eligibleProfessionals->count();

            $debugInfo['eligible_count'] = $eligibleProfessionalsCount;
            $debugInfo['sql_query'] = $query->toSql();

            // Récupérer les utilisateurs éligibles
            $eligibleUsers = collect();
            foreach ($eligibleProfessionals as $profile) {
                if ($profile->user && $profile->user->is_professional) {
                    $eligibleUsers->push($profile->user);
                    $debugInfo['results'][] = [
                        'profile_id' => $profile->id,
                        'user_id' => $profile->user->id,
                        'name' => $profile->first_name . ' ' . $profile->last_name,
                        'city' => $profile->city,
                        'experience' => $profile->years_of_experience,
                        'availability' => $profile->availability_status,
                        'skills' => is_string($profile->skills) ? json_decode($profile->skills, true) : $profile->skills,
                        'languages' => is_string($profile->languages) ? json_decode($profile->languages, true) : $profile->languages,
                    ];
                }
            }

            $debugInfo['eligible_users_count'] = $eligibleUsers->count();

            return response()->json([
                'debug_info' => $debugInfo,
                'message' => 'Debug du matching terminé avec succès.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du debug du matching: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors du debug du matching.'
            ], 500);
        }
    }
}
