<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Events\MetadataEdited;
use App\Events\PersonTagged;
use App\Events\PhotoUploaded;
use App\Events\PhotoVoted;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\PointAction;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\Vote;

class AwardPoints
{
    /**
     * Map of event classes to their point action keys and actionable resolvers.
     *
     * @var array<class-string, array{key: string, type: class-string, id: string}>
     */
    protected array $eventMap = [
        PhotoUploaded::class => ['key' => 'photo_uploaded', 'type' => Photo::class, 'id' => 'photoId'],
        MetadataEdited::class => ['key' => 'metadata_edited', 'type' => Photo::class, 'id' => 'photoId'],
        CommentCreated::class => ['key' => 'comment_created', 'type' => Comment::class, 'id' => 'commentId'],
        PhotoVoted::class => ['key' => 'photo_voted', 'type' => Vote::class, 'id' => 'photoId'],
        PersonTagged::class => ['key' => 'person_tagged', 'type' => Photo::class, 'id' => 'photoId'],
    ];

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $eventClass = $event::class;

        if (! isset($this->eventMap[$eventClass])) {
            return;
        }

        $mapping = $this->eventMap[$eventClass];
        $pointAction = PointAction::query()->where('key', $mapping['key'])->first();

        if (! $pointAction) {
            return;
        }

        $transaction = PointTransaction::firstOrCreate(
            [
                'user_id' => $event->userId,
                'point_action_id' => $pointAction->id,
                'actionable_type' => $mapping['type'],
                'actionable_id' => $event->{$mapping['id']},
            ],
            [
                'points' => $pointAction->points,
            ],
        );

        if ($transaction->wasRecentlyCreated) {
            User::query()->where('id', $event->userId)->increment('total_points', $pointAction->points);
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
