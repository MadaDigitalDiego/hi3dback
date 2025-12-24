<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenOffer;
use App\Models\Message;
use App\Models\User; 
use App\Models\NotifMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Notifications\NewMessageNotification; // Importez la nouvelle notification
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log; // Importez la facade Log


class MessageController extends Controller
{
    /**
     * Get messages for a specific open offer and professional (conversation).
     */
    public function index(OpenOffer $openOffer, Request $request): JsonResponse
    {
        $professionalId = $request->query('professional_id'); // Récupérer l'ID du professionnel depuis la query string

        // Validation: professional_id est requis si l'utilisateur n'est pas le créateur de l'offre
        if ($openOffer->user_id !== auth()->id() && !$professionalId) {
            return response()->json(['message' => 'professional_id est requis pour voir les messages en tant que professionnel.'], 400);
        }

        // Authorization:
        if ($openOffer->user_id !== auth()->id()) { // Si ce n'est pas le créateur de l'offre (client)
            if (!auth()->user()->is_professional) { // S'assurer que c'est un professionnel qui essaie d'accéder
                return response()->json(['message' => 'Non autorisé à voir les messages pour cette offre.'], 403);
            }

            // Vérifier si le professionnel a accès à cette conversation (candidature acceptée ou client a initié)
            $hasAcceptedApplication = $openOffer->applications()->whereHas('freelanceProfile.user', function ($query) {
                $query->where('id', auth()->id());
            })->where('status', 'accepted')->exists();

            $hasClientMessage = Message::where('open_offer_id', $openOffer->id)
                ->where('sender_id', $openOffer->user_id) // Client est l'expéditeur
                ->where('receiver_id', auth()->id()) // Professionnel est le destinataire
                ->exists();

            if (!$hasAcceptedApplication && !$hasClientMessage) {
                return response()->json(['message' => 'Non autorisé à voir les messages. Le chat s\'ouvre après que le client ait envoyé le premier message ou après acceptation de votre candidature.'], 403);
            }
        }

        try {
            $messages = Message::with(['sender', 'receiver', 'files']) // Charger aussi le receiver et les fichiers
                ->where('open_offer_id', $openOffer->id);


            // Filtrer par professionnel si professional_id est fourni (pour conversation privée)
            if ($professionalId) {
                $messages->where(function ($query) use ($professionalId, $openOffer) {
                    $query->where(function ($q) use ($professionalId, $openOffer) { // Messages du client vers le professionnel
                        $q->where('sender_id', $openOffer->user_id)
                          ->where('receiver_id', $professionalId);
                    })->orWhere(function ($q) use ($professionalId, $openOffer) { // Messages du professionnel vers le client
                        $q->where('sender_id', $professionalId)
                          ->where('receiver_id', $openOffer->user_id);
                    });
                });
            }

            // $messages = $messages->latest()->get();
            $messages = $messages->orderBy('created_at', 'asc')->get();


            return response()->json(['messages' => $messages]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des messages pour l\'offre ouverte ID ' . $openOffer->id . ' et professionnel ID ' . $professionalId . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des messages.'], 500);
        }
    }

    /**
     * Store a new message for an open offer, potentially directed to a specific professional.
     */
    public function store(Request $request, OpenOffer $openOffer): JsonResponse
    {
	        $receiverId = $request->input('receiver_id'); // Récupérer le receiver_id depuis le body de la requête
	        $user = auth()->user();

        // Validation
        $validator = Validator::make($request->all(), [
            'message_text' => 'nullable|string',
            'receiver_id' => 'nullable|exists:users,id,is_professional,1', // Valider que receiver_id est un professionnel existant
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

	        // Authorization:
	        $isClient = $openOffer->user_id === ($user?->id);
	        $isProfessional = $user && $user->is_professional;

        if (!$isClient && !$isProfessional) {
            return response()->json(['message' => 'Non autorisé à envoyer des messages pour cette offre.'], 403);
        }

	        if ($isClient) {
            // Client sending first message to a professional
            if (!$receiverId) {
                return response()->json(['message' => 'receiver_id est requis pour envoyer le premier message à un professionnel.'], 400);
            }
            // Vérifier si le receiver_id est bien un professionnel qui a postulé ou a été invité (vous pouvez ajouter une logique plus précise ici si nécessaire)
            $isInterestedProfessional = $openOffer->applications()->whereHas('freelanceProfile.user', function ($query) use ($receiverId) {
                $query->where('id', $receiverId);
            })->exists() || $openOffer->professionals()->where('user_id', $receiverId)->exists(); // Vérifie aussi si invité

            if (!$isInterestedProfessional) {
                return response()->json(['message' => 'Le receiver_id spécifié n\'est pas un professionnel valide pour cette offre.'], 400);
            }

	        } elseif ($isProfessional) {
            // Professional replying to a client
            $hasClientMessage = Message::where('open_offer_id', $openOffer->id)
                ->where('sender_id', $openOffer->user_id) // Client est l'expéditeur initial
                ->where('receiver_id', auth()->id()) // Professionnel est le destinataire initial
                ->exists();

            $hasAcceptedApplication = $openOffer->applications()->whereHas('freelanceProfile.user', function ($query) {
                $query->where('id', auth()->id());
            })->where('status', 'accepted')->exists();

            if (!$hasClientMessage && !$hasAcceptedApplication) {
                return response()->json(['message' => 'Non autorisé à envoyer des messages initialement. Le client doit envoyer le premier message pour ouvrir le chat.'], 403);
            }
            $receiverId = $openOffer->user_id; // Professional replies to the client (offer creator)
	        }

		        // Check subscription/message limits for the authenticated user
		        if (!$user || !$user->canPerformAction('messages')) {
		            $subscription = $user?->currentSubscription();
		            $message = $subscription
		                ? 'Vous avez atteint la limite d\'envoi de messages pour votre abonnement. Veuillez mettre à niveau votre plan.'
		                : 'Plan Free actif. Un abonnement est requis pour accéder à toutes les fonctionnalités.';
		            $errorType = $subscription ? 'QUOTA_EXCEEDED' : 'NO_SUBSCRIPTION';

		            return response()->json([
		                'message' => $message,
		                'error_code' => 'MESSAGES_LIMIT_REACHED',
		                'error_type' => $errorType,
		            ], 403);
		        }

	        try {
            $message = Message::create([
                'open_offer_id' => $openOffer->id,
	                'sender_id' => $user->id,
                'receiver_id' => $receiverId, // Utiliser le receiver_id spécifié
                'message_text' => $request->message_text,
            ]);

            $message->load('sender', 'receiver'); // Charger aussi le receiver

            // ✅ Enregistrement dans notif_messages avant l'envoi du mail
            $notif = NotifMessage::create([
                'message_id'     => $message->id,
                'sender_id'  => $message->sender_id,
                'receiver_id'=> $message->receiver_id,
                'offer_id'       => $openOffer->id,
                'title'          => 'Nouveau message concernant l\'offre: ' . $openOffer->title,
                'is_read'   => false,
            ]);

            // Envoi de la notification au receiver
            $receiverUser = User::find($receiverId);

            if ($receiverUser) {
                Notification::send($receiverUser, new NewMessageNotification($message));
            }


            return response()->json(['message' => $message, 'message_str' => 'Message envoyé avec succès.'], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement du message pour l\'offre ouverte ID ' . $openOffer->id . ', receiver ID ' . $receiverId . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'envoi du message.'], 500);
        }
    }
}
