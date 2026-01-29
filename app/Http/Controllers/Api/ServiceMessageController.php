<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\OpenOffer;
use App\Models\ServiceOffer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Notifications\NewMessageNotification; // Importez la nouvelle notification
use Illuminate\Support\Facades\Notification;

class ServiceMessageController extends Controller
{
    /**
     * Send a message to a professional about a service.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'recipient_id' => 'required|exists:users,id',
                'content' => 'required|string|max:1000',
                'service_id' => 'required|exists:service_offers,id',
            ]);

            // Log the request data for debugging
            Log::info('Message request data:', $request->all());

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Get authenticated user
            $user = Auth::user();

            // Enforce subscription message limits for professionals only
            // Clients have unlimited quotas
            if ($user && $user->is_professional && !$user->canPerformAction('messages')) {
                $subscription = $user->currentSubscription();
                $message = $subscription
                    ? 'You have reached the invitation limit for your subscription. Please upgrade your plan.'
                    : 'You must have an active subscription to perform this action.';
                $errorType = $subscription ? 'QUOTA_EXCEEDED' : 'NO_SUBSCRIPTION';

                return response()->json([
                    'message' => $message,
                    'error_code' => 'MESSAGES_LIMIT_REACHED',
                    'error_type' => $errorType,
                ], 403);
            }

            // Check if service exists
            $service = ServiceOffer::findOrFail($request->service_id);

            // Check if recipient exists
            $recipient = User::findOrFail($request->recipient_id);

            // Prepare message text with metadata
            // $messageText = json_encode([
            //     'content' => $request->content,
            //     'service_id' => $request->service_id,
            //     'type' => 'service_message'
            // ]);

            $messageText = $request->content;

            // Find or create an open offer for service messages
            $serviceMessageOffer = OpenOffer::firstOrCreate(
                ['title' => 'Service Messages'],
                [
                    'description' => 'This offer is used for service messages',
                    'budget' => 0,
                    'status' => 'open',
                    'user_id' => 1, // Admin user ID
                ]
            );

            // Create message
            $message = Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $request->recipient_id,
                'message_text' => $messageText,
                'open_offer_id' => $serviceMessageOffer->id,
            ]);

            $message->load('sender', 'receiver');

            // Send notification to receiver
            $receiverUser = User::find($request->recipient_id);
            if ($receiverUser) {
                Notification::send($receiverUser, new NewMessageNotification($message));
            }

            return response()->json([
                'message' => 'Message sent successfully',
                'data' => $message,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json(['message' => 'Error sending message: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get all messages for the authenticated user.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Get all messages where user is sender or recipient
            $messages = Message::where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id)
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => $messages,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting messages: ' . $e->getMessage());
            return response()->json(['message' => 'Error getting messages'], 500);
        }
    }

    /**
     * Get all messages for a specific service.
     *
     * @param int $serviceId
     * @return JsonResponse
     */
    public function getServiceMessages(int $serviceId): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if service exists
            $service = ServiceOffer::findOrFail($serviceId);

            // Get all messages between the user and the service owner
            // We need to filter manually since we store the service ID in message_text
            $messages = Message::where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                        ->orWhere('receiver_id', $user->id);
                })
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'asc')
                ->get();

            // Filter messages to keep only those related to this service
            $filteredMessages = $messages->filter(function ($message) use ($serviceId) {
                try {
                    $messageData = json_decode($message->message_text, true);
                    return isset($messageData['service_id']) && $messageData['service_id'] == $serviceId;
                } catch (\Exception $e) {
                    return false;
                }
            });

            return response()->json([
                'data' => $filteredMessages->values(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting service messages: ' . $e->getMessage());
            return response()->json(['message' => 'Error getting service messages'], 500);
        }
    }

    /**
     * Mark a message as read.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function markAsRead(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            // Find message
            $message = Message::where('id', $id)
                ->where('receiver_id', $user->id)
                ->firstOrFail();

            // Mark as read
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            return response()->json([
                'message' => 'Message marked as read',
                'data' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking message as read: ' . $e->getMessage());
            return response()->json(['message' => 'Error marking message as read'], 500);
        }
    }

    /**
     * Get conversation with a specific user.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getConversation(int $userId): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if other user exists
            $otherUser = User::findOrFail($userId);

            $messages = Message::select('*', DB::raw('LEAST(sender_id, receiver_id) as user1'), DB::raw('GREATEST(sender_id, receiver_id) as user2'))
                ->where(function ($query) use ($userId) {
                    $query->where('sender_id', $userId)
                        ->orWhere('receiver_id', $userId);
                })
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'asc')
                ->get()
                ->groupBy(function ($message) {
                    return $message->user1 . '_' . $message->user2;
                })
                ->values();

            return response()->json([
                'data' => $messages,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting conversation: ' . $e->getMessage());
            return response()->json(['message' => 'Error getting conversation'], 500);
        }
    }
}

