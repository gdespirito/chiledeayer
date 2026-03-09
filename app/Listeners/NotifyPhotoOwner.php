<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Events\MetadataEdited;
use App\Events\PersonTagged;
use App\Events\PhotoVoted;
use App\Models\Comment;
use App\Models\Person;
use App\Models\Photo;
use App\Models\User;
use App\Notifications\PersonTaggedOnPhotoNotification;
use App\Notifications\PhotoCommentedNotification;
use App\Notifications\PhotoEditedNotification;
use App\Notifications\PhotoVotedNotification;
use Illuminate\Support\Facades\Cache;

class NotifyPhotoOwner
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        match ($event::class) {
            CommentCreated::class => $this->handleCommentCreated($event),
            MetadataEdited::class => $this->handleMetadataEdited($event),
            PhotoVoted::class => $this->handlePhotoVoted($event),
            PersonTagged::class => $this->handlePersonTagged($event),
            default => null,
        };
    }

    /**
     * Handle a comment created event.
     */
    protected function handleCommentCreated(CommentCreated $event): void
    {
        $photo = Photo::find($event->photoId);

        if (! $photo || $event->userId === $photo->user_id) {
            return;
        }

        $owner = User::find($photo->user_id);
        $comment = Comment::find($event->commentId);
        $actor = User::find($event->userId);

        if (! $owner || ! $comment || ! $actor) {
            return;
        }

        $sendMail = ! $this->shouldThrottleMail($photo->id);

        $owner->notify(new PhotoCommentedNotification(
            $photo->id,
            $actor->name,
            $comment->body,
            $sendMail,
        ));

        if ($sendMail) {
            $this->markMailSent($photo->id);
        }
    }

    /**
     * Handle a metadata edited event.
     */
    protected function handleMetadataEdited(MetadataEdited $event): void
    {
        $photo = Photo::find($event->photoId);

        if (! $photo || $event->userId === $photo->user_id) {
            return;
        }

        $owner = User::find($photo->user_id);
        $actor = User::find($event->userId);

        if (! $owner || ! $actor) {
            return;
        }

        $sendMail = ! $this->shouldThrottleMail($photo->id);

        $owner->notify(new PhotoEditedNotification(
            $photo->id,
            $actor->name,
            $event->changes,
            $sendMail,
        ));

        if ($sendMail) {
            $this->markMailSent($photo->id);
        }
    }

    /**
     * Handle a photo voted event.
     */
    protected function handlePhotoVoted(PhotoVoted $event): void
    {
        $photo = Photo::find($event->photoId);

        if (! $photo || $event->userId === $photo->user_id) {
            return;
        }

        $owner = User::find($photo->user_id);
        $actor = User::find($event->userId);

        if (! $owner || ! $actor) {
            return;
        }

        $owner->notify(new PhotoVotedNotification(
            $photo->id,
            $actor->name,
            $event->value,
        ));
    }

    /**
     * Handle a person tagged event.
     */
    protected function handlePersonTagged(PersonTagged $event): void
    {
        $photo = Photo::find($event->photoId);

        if (! $photo || $event->userId === $photo->user_id) {
            return;
        }

        $owner = User::find($photo->user_id);
        $actor = User::find($event->userId);
        $person = Person::find($event->personId);

        if (! $owner || ! $actor || ! $person) {
            return;
        }

        $sendMail = ! $this->shouldThrottleMail($photo->id);

        $owner->notify(new PersonTaggedOnPhotoNotification(
            $photo->id,
            $actor->name,
            $person->name,
            $sendMail,
        ));

        if ($sendMail) {
            $this->markMailSent($photo->id);
        }
    }

    /**
     * Check if mail should be throttled for a given photo.
     */
    protected function shouldThrottleMail(int $photoId): bool
    {
        $hour = now()->format('YmdH');

        return Cache::has("notif:mail:{$photoId}:{$hour}");
    }

    /**
     * Mark that a mail was sent for a given photo in the current hour.
     */
    protected function markMailSent(int $photoId): void
    {
        $hour = now()->format('YmdH');

        Cache::put("notif:mail:{$photoId}:{$hour}", true, now()->endOfHour());
    }

    /**
     * Get the events this listener should subscribe to.
     *
     * @return array<class-string, string>
     */
    public function subscribe(): array
    {
        return [
            CommentCreated::class => 'handle',
            MetadataEdited::class => 'handle',
            PhotoVoted::class => 'handle',
            PersonTagged::class => 'handle',
        ];
    }
}
