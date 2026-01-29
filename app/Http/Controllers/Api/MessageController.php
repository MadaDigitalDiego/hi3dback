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
        $professionalId = $request->query('professional_id'); // Get professional ID from query string

        // Validation: professional_id is required if user is not the offer creator
        if ($openOffer->user_id !== auth()->id() && !$professionalId) {
            return response()->json(['message' => 'professional_id is required to view messages as a professional.'], 400);
        }

        // Authorization:
        if ($openOffer->user_id !== auth()->id()) { // If not the offer creator (client)
            if (!auth()->user()->is_professional) { // Ensure it's a professional trying to access
                return response()->json(['message' => 'Not authorized to view messages for this offer.'], 403);
            }

            // Check if professional has access to this conversation (accepted application or client initiated)
            $hasAcceptedApplication = $openOffer->applications()->whereHas('freelanceProfile.user', function ($query) {
                $query->where('id', auth()->id());
            })->where('status', 'accepted')->exists();

            $hasClientMessage = Message::where('open_offer_id', $openOffer->id)
                ->where('sender_id', $openOffer->user_id) // Client is sender
                ->where('receiver_id', auth()->id()) // Professional is receiver
                ->exists();

            if (!$hasAcceptedApplication && !$hasClientMessage) {
                return response()->json(['message' => 'Not authorized to view messages. Chat opens after client sends first message or after your application is accepted.'], 403);
            }
        }

        try {
            $messages = Message::with(['sender', 'receiver', 'files']) // Also load receiver and files
                ->where('open_offer_id', $openOffer->id);


            // Filter by professional if professional_id is provided (for private conversation)
            if ($professionalId) {
                $messages->where(function ($query) use ($professionalId, $openOffer) {
                    $query->where(function ($q) use ($professionalId, $openOffer) { // Messages from client to professional
                        $q->where('sender_id', $openOffer->user_id)
                          ->where('receiver_id', $professionalId);
                    })->orWhere(function ($q) use ($professionalId, $openOffer) { // Messages from professional to client
                        $q->where('sender_id', $professionalId)
                          ->where('receiver_id', $openOffer->user_id);
                    });
                });
            }

            // $messages = $messages->latest()->get();
            $messages = $messages->orderBy('created_at', 'asc')->get();


            return response()->json(['messages' => $messages]);
        } catch (\Exception $e) {
            Log::error('Error retrieving messages for open offer ID ' . $openOffer->id . ' and professional ID ' . $professionalId . ': ' . $e->getMessage());
            return response()->json(['message' => 'Error retrieving messages.'], 500);
        }
    }

    /**
     * Store a new message for an open offer, potentially directed to a specific professional.
     */
    public function store(Request $request, OpenOffer $openOffer): JsonResponse
    {
	        $receiverId = $request->input('receiver_id'); // Get receiver_id from request body
	        $user = auth()->user();

        // Validation
        $validator = Validator::make($request->all(), [
            'message_text' => 'nullable|string',
            'receiver_id' => 'nullable|exists:users,id,is_professional,1', // Validate that receiver_id is an existing professional
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

	        // Authorization:
	        $isClient = $openOffer->user_id === ($user?->id);
	        $isProfessional = $user && $user->is_professional;

        if (!$isClient && !$isProfessional) {
            return response()->json(['message' => 'Not authorized to send messages for this offer.'], 403);
	        }

	        if ($isClient) {
            // Client sending first message to a professional
            if (!$receiverId) {
                return response()->json(['message' => 'receiver_id is required to send the first message to a professional.'], 400);
            }
            // Check if receiver_id is a professional who has applied or been invited
            $isInterestedProfessional = $openOffer->applications()->whereHas('freelanceProfile.user', function ($query) use ($receiverId) {
                $query->where('id', $receiverId);
            })->exists() || $openOffer->professionals()->where('user_id', $receiverId)->exists();

            if (!$isInterestedProfessional) {
                return response()->json(['message' => 'The specified receiver_id is not a valid professional for this offer.'], 400);
            }

	        } elseif ($isProfessional) {
            // Professional replying to a client
            $hasClientMessage = Message::where('open_offer_id', $openOffer->id)
                ->where('sender_id', $openOffer->user_id) // Client is initial sender
                ->where('receiver_id', auth()->id()) // Professional is initial receiver
                ->exists();

            $hasAcceptedApplication = $openOffer->applications()->whereHas('freelanceProfile.user', function ($query) {
                $query->where('id', auth()->id());
            })->where('status', 'accepted')->exists();

            if (!$hasClientMessage && !$hasAcceptedApplication) {
                return response()->json(['message' => 'Not authorized to send messages initially. Client must send the first message to open the chat.'], 403);
            }
	        $receiverId = $openOffer->user_id; // Professional replies to the client (offer creator)
	        }

		        // Check subscription/message limits for the authenticated user
		        // Note: Clients have unlimited quotas, so skip this check for them
		        if ($user && $user->is_professional && !$user->canPerformAction('messages')) {
		            $subscription = $user->currentSubscription();
		            $message = $subscription
		                ? 'You have reached the message sending limit for your subscription. Please upgrade your plan.'
		                : 'Free plan active. A subscription is required to access all features.';
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
                'receiver_id' => $receiverId, // Use specified receiver_id
                'message_text' => $request->message_text,
            ]);

            $message->load('sender', 'receiver'); // Also load receiver

            // ✅ Save to notif_messages before sending email
            $notif = NotifMessage::create([
                'message_id'     => $message->id,
                'sender_id'  => $message->sender_id,
                'receiver_id'=> $message->receiver_id,
                'offer_id'       => $openOffer->id,
                'title'          => 'New message regarding offer: ' . $openOffer->title,
                'is_read'   => false,
            ]);

            // Send notification to receiver
            $receiverUser = User::find($receiverId);

            if ($receiverUser) {
                Notification::send($receiverUser, new NewMessageNotification($message));
            }


            return response()->json(['message' => $message, 'message_str' => 'Message sent successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error saving message for open offer ID ' . $openOffer->id . ', receiver ID ' . $receiverId . ': ' . $e->getMessage());
            return response()->json(['message' => 'Error sending message.'], 500);
        }
    }
}
