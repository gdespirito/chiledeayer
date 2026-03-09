<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Events\MetadataEdited;
use App\Events\PersonTagged;
use App\Events\PhotoUploaded;
use App\Events\PhotoVoted;
use App\Models\Badge;
use App\Models\PointTransaction;
use App\Models\User;

class EvaluateBadges
{
    /**
     * Badge criteria: key => callable that checks if the user qualifies.
     *
     * @var array<string, callable(User): bool>
     */
    protected array $criteria;

    public function __construct()
    {
        $this->criteria = [
            'first_upload' => fn (User $user): bool => $user->photos()->count() >= 1,
            'ten_uploads' => fn (User $user): bool => $user->photos()->count() >= 10,
            'fifty_uploads' => fn (User $user): bool => $user->photos()->count() >= 50,
            'first_comment' => fn (User $user): bool => $user->comments()->count() >= 1,
            'first_edit' => fn (User $user): bool => PointTransaction::query()
                ->where('user_id', $user->id)
                ->whereHas('pointAction', fn ($q) => $q->where('key', 'metadata_edited'))
                ->exists(),
            'first_person_tag' => fn (User $user): bool => PointTransaction::query()
                ->where('user_id', $user->id)
                ->whereHas('pointAction', fn ($q) => $q->where('key', 'person_tagged'))
                ->exists(),
        ];
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $user = User::find($event->userId);

        if (! $user) {
            return;
        }

        $earnedBadgeKeys = $user->badges()->pluck('key')->all();

        foreach ($this->criteria as $badgeKey => $check) {
            if (in_array($badgeKey, $earnedBadgeKeys, true)) {
                continue;
            }

            if (! $check($user)) {
                continue;
            }

            $badge = Badge::query()->where('key', $badgeKey)->first();

            if (! $badge) {
                continue;
            }

            $user->badges()->attach($badge->id, ['awarded_at' => now()]);

            if ($badge->points_awarded > 0) {
                $user->increment('total_points', $badge->points_awarded);
            }
        }
    }

    /**
     * Get the events this listener should subscribe to.
     *
     * @return array<class-string, string>
     */
    public function subscribe(): array
    {
        return [
            PhotoUploaded::class => 'handle',
            MetadataEdited::class => 'handle',
            CommentCreated::class => 'handle',
            PhotoVoted::class => 'handle',
            PersonTagged::class => 'handle',
        ];
    }
}
