<?php

use App\Events\CommentCreated;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('guests cannot create comments', function () {
    $photo = Photo::factory()->create();

    $response = $this->post(route('photos.comments.store', $photo), [
        'body' => 'Test comment',
    ]);

    $response->assertRedirect(route('login'));
});

test('authenticated users can create comments', function () {
    Event::fake(CommentCreated::class);

    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.comments.store', $photo), [
        'body' => 'This is a great photo!',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('comments', [
        'body' => 'This is a great photo!',
        'photo_id' => $photo->id,
        'user_id' => $user->id,
    ]);

    Event::assertDispatched(CommentCreated::class, function ($event) use ($photo, $user) {
        return $event->photoId === $photo->id && $event->userId === $user->id;
    });
});

test('comment body is required', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.comments.store', $photo), [
        'body' => '',
    ]);

    $response->assertSessionHasErrors('body');
});

test('comment body cannot exceed 2000 characters', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.comments.store', $photo), [
        'body' => str_repeat('a', 2001),
    ]);

    $response->assertSessionHasErrors('body');
});

test('comment author can delete their own comment', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete(route('comments.destroy', $comment));

    $response->assertRedirect();
    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
});

test('users cannot delete comments by other users', function () {
    $author = User::factory()->create();
    $otherUser = User::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $author->id]);

    $response = $this->actingAs($otherUser)->delete(route('comments.destroy', $comment));

    $response->assertForbidden();
    $this->assertDatabaseHas('comments', ['id' => $comment->id]);
});
