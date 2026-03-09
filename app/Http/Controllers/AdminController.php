<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AdminController extends Controller
{
    /**
     * Soft delete a photo (admin only).
     */
    public function deletePhoto(Photo $photo): JsonResponse
    {
        Gate::authorize('admin');

        $photo->delete();

        return response()->json(['message' => 'Foto eliminada.']);
    }

    /**
     * Delete a comment (admin only).
     */
    public function deleteComment(Comment $comment): JsonResponse
    {
        Gate::authorize('admin');

        $comment->delete();

        return response()->json(['message' => 'Comentario eliminado.']);
    }

    /**
     * Ban a user and soft delete all their photos.
     */
    public function banUser(User $user): JsonResponse
    {
        Gate::authorize('admin');

        $user->update(['is_banned' => true]);
        $user->photos()->delete();

        return response()->json(['message' => 'Usuario suspendido.']);
    }

    /**
     * Unban a user.
     */
    public function unbanUser(User $user): JsonResponse
    {
        Gate::authorize('admin');

        $user->update(['is_banned' => false]);

        return response()->json(['message' => 'Usuario reactivado.']);
    }
}
