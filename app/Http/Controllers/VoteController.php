<?php

namespace App\Http\Controllers;

use App\Events\PhotoVoted;
use App\Http\Requests\StoreVoteRequest;
use App\Models\Photo;
use App\Models\Vote;
use Illuminate\Http\RedirectResponse;

class VoteController extends Controller
{
    /**
     * Toggle a vote on a photo (create or update).
     */
    public function store(StoreVoteRequest $request, Photo $photo): RedirectResponse
    {
        $value = $request->validated('value');

        $vote = Vote::query()
            ->where('photo_id', $photo->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($vote && $vote->value === (int) $value) {
            $vote->delete();
            $this->syncVoteCounts($photo);

            return back()->with('success', 'Voto removido.');
        }

        Vote::query()->updateOrCreate(
            [
                'photo_id' => $photo->id,
                'user_id' => $request->user()->id,
            ],
            ['value' => $value],
        );

        $this->syncVoteCounts($photo);

        PhotoVoted::dispatch($photo->id, $request->user()->id, (int) $value);

        return back()->with('success', 'Voto registrado.');
    }

    /**
     * Recalculate and persist cached vote counts on the photo.
     */
    private function syncVoteCounts(Photo $photo): void
    {
        $photo->update([
            'upvotes_count' => $photo->votes()->where('value', 1)->count(),
            'downvotes_count' => $photo->votes()->where('value', -1)->count(),
        ]);
    }
}
