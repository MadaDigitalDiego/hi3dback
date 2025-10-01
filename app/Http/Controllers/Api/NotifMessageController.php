<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotifMessage;
use Illuminate\Http\Request;

class NotifMessageController extends Controller
{
    /**
     * Liste des notifications de l’utilisateur connecté
     */
    public function index(Request $request)
    {
        $user = $request->user(); // récupère l'utilisateur connecté

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $notifications = NotifMessage::with(['sender', 'offer'])
            ->where('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $notif = NotifMessage::findOrFail($id);

        // ✅ Vérifier que c’est bien le receiver
        if ($notif->receiver_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notif->is_read = true;
        $notif->read_at = now();
        $notif->save();

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Compter les notifications non lues
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $count = NotifMessage::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();

        $notif = NotifMessage::where('id', $id)->where('receiver_id', $user->id)->first();
        if (!$notif) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notif->delete();

        return response()->json(['message' => 'Notification deleted']);
    }
}
