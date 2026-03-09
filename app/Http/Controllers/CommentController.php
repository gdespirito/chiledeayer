<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Store a new comment on a photo.
     */
    public function store(StoreCommentRequest $request, Photo $photo): RedirectResponse
    {
        $comment = $photo->comments()->create([
            'body' => $request->validated('body'),
            'user_id' => $request->user()->id,
        ]);

        CommentCreated::dispatch($comment->id, $photo->id, $request->user()->id);

        return back()->with('success', 'Comentario agregado.');
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Comentario eliminado.');
    }
}
