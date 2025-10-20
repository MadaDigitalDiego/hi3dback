<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FilePolicy
{
    /**
     * Determine whether the user can view the file.
     * 
     * Allowed:
     * - Owner (user_id)
     * - Receiver (receiver_id)
     * - Admin/Super-admin
     */
    public function view(User $user, File $file): Response
    {
        // Admin can always view
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return Response::allow();
        }

        // Owner can view
        if ($file->user_id === $user->id) {
            return Response::allow();
        }

        // Receiver can view
        if ($file->receiver_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view this file.');
    }

    /**
     * Determine whether the user can download the file.
     * 
     * Allowed:
     * - Owner (user_id)
     * - Receiver (receiver_id)
     * - Admin/Super-admin
     */
    public function download(User $user, File $file): Response
    {
        // Admin can always download
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return Response::allow();
        }

        // Owner can download
        if ($file->user_id === $user->id) {
            return Response::allow();
        }

        // Receiver can download
        if ($file->receiver_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to download this file.');
    }

    /**
     * Determine whether the user can delete the file.
     * 
     * Allowed:
     * - Owner (user_id) only
     * - Admin/Super-admin
     */
    public function delete(User $user, File $file): Response
    {
        // Admin can always delete
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return Response::allow();
        }

        // Only owner can delete
        if ($file->user_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('Only the file owner can delete this file.');
    }

    /**
     * Determine whether the user can update the file.
     * 
     * Allowed:
     * - Owner (user_id) only
     * - Admin/Super-admin
     */
    public function update(User $user, File $file): Response
    {
        // Admin can always update
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return Response::allow();
        }

        // Only owner can update
        if ($file->user_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('Only the file owner can update this file.');
    }

    /**
     * Determine whether the user can share the file.
     * 
     * Allowed:
     * - Owner (user_id) only
     * - Admin/Super-admin
     */
    public function share(User $user, File $file): Response
    {
        // Admin can always share
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return Response::allow();
        }

        // Only owner can share
        if ($file->user_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('Only the file owner can share this file.');
    }

    /**
     * Determine whether the user can access file in a message context.
     * 
     * Allowed:
     * - Owner of the file
     * - Receiver of the file
     * - Sender of the message (if file is in a message)
     * - Receiver of the message (if file is in a message)
     * - Admin/Super-admin
     */
    public function viewInMessage(User $user, File $file): Response
    {
        // Admin can always view
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return Response::allow();
        }

        // Owner can view
        if ($file->user_id === $user->id) {
            return Response::allow();
        }

        // Receiver can view
        if ($file->receiver_id === $user->id) {
            return Response::allow();
        }

        // If file is associated with a message, check message participants
        if ($file->message_id) {
            $message = $file->message;
            if ($message) {
                // Sender of message can view
                if ($message->sender_id === $user->id) {
                    return Response::allow();
                }

                // Receiver of message can view
                if ($message->receiver_id === $user->id) {
                    return Response::allow();
                }
            }
        }

        return Response::deny('You do not have permission to view this file.');
    }

    /**
     * Determine whether the user can access file in a fileable context.
     * 
     * Allowed:
     * - Owner of the file
     * - Owner of the parent (fileable)
     * - Admin/Super-admin
     */
    public function viewInFileable(User $user, File $file): Response
    {
        // Admin can always view
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return Response::allow();
        }

        // Owner can view
        if ($file->user_id === $user->id) {
            return Response::allow();
        }

        // Check if user owns the parent (fileable)
        if ($file->fileable) {
            if (method_exists($file->fileable, 'user_id') && $file->fileable->user_id === $user->id) {
                return Response::allow();
            }
        }

        return Response::deny('You do not have permission to view this file.');
    }
}

