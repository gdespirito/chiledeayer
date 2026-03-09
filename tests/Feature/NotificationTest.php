<?php

use App\Events\CommentCreated;
use App\Events\MetadataEdited;
use App\Events\PhotoVoted;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\User;
use App\Notifications\PhotoCommentedNotification;
use App\Notifications\PhotoEditedNotification;
use App\Notifications\PhotoVotedNotification;
use Illuminate\Support\Facades\Notification;

test('photo owner receives notification when someone comments', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $photo = Photo::factory()->for($owner)->create();
    $comment = Comment::factory()->for($photo)->for($commenter)->create();

    CommentCreated::dispatch($comment->id, $photo->id, $commenter->id);

    Notification::assertSentTo($owner, PhotoCommentedNotification::class, function ($notification) use ($photo, $commenter) {
        return $notification->photoId === $photo->id
            && $notification->commenterName === $commenter->name;
    });
});

test('photo owner receives notification when someone edits metadata', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $editor = User::factory()->create();
    $photo = Photo::factory()->for($owner)->create();

    MetadataEdited::dispatch($photo->id, $editor->id, ['title' => 'updated']);

    Notification::assertSentTo($owner, PhotoEditedNotification::class, function ($notification) use ($photo, $editor) {
        return $notification->photoId === $photo->id
            && $notification->editorName === $editor->name;
    });
});

test('photo owner receives notification when someone votes', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $voter = User::factory()->create();
    $photo = Photo::factory()->for($owner)->create();

    PhotoVoted::dispatch($photo->id, $voter->id, 1);

    Notification::assertSentTo($owner, PhotoVotedNotification::class, function ($notification) use ($photo, $voter) {
        return $notification->photoId === $photo->id
            && $notification->voterName === $voter->name
            && $notification->voteValue === 1;
    });
});

test('photo owner does NOT receive notification for own actions', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $photo = Photo::factory()->for($owner)->create();
    $comment = Comment::factory()->for($photo)->for($owner)->create();

    CommentCreated::dispatch($comment->id, $photo->id, $owner->id);

    Notification::assertNothingSent();
});

test('notifications can be marked as read', function () {
    $user = User::factory()->create();

    $user->notify(new PhotoVotedNotification(1, 'Test User', 1));

    expect($user->unreadNotifications()->count())->toBe(1);

    $notification = $user->notifications()->first();

    $response = $this->actingAs($user)->postJson(
        route('api.notifications.read', $notification->id)
    );

    $response->assertOk();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('all notifications can be marked as read', function () {
    $user = User::factory()->create();

    $user->notify(new PhotoVotedNotification(1, 'User A', 1));
    $user->notify(new PhotoVotedNotification(2, 'User B', -1));

    expect($user->unreadNotifications()->count())->toBe(2);

    $response = $this->actingAs($user)->postJson(
        route('api.notifications.read-all')
    );

    $response->assertOk();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('notification index returns user notifications', function () {
    $user = User::factory()->create();

    $user->notify(new PhotoVotedNotification(1, 'Test User', 1));

    $response = $this->actingAs($user)->getJson(
        route('api.notifications.index')
    );

    $response->assertOk();
    $response->assertJsonCount(1, 'notifications');
    $response->assertJsonPath('unread_count', 1);
});

test('guests cannot access notification endpoints', function () {
    $this->getJson(route('api.notifications.index'))->assertUnauthorized();
    $this->postJson(route('api.notifications.read-all'))->assertUnauthorized();
    $this->postJson(route('api.notifications.read', 'fake-id'))->assertUnauthorized();
});
