<?php

use App\Events\PhotoVoted;
use App\Models\Photo;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\Event;

test('guests cannot vote on photos', function () {
    $photo = Photo::factory()->create();

    $response = $this->post(route('photos.vote.store', $photo), [
        'value' => 1,
    ]);

    $response->assertRedirect(route('login'));
});

test('authenticated users can upvote a photo', function () {
    Event::fake(PhotoVoted::class);

    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.vote.store', $photo), [
        'value' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('votes', [
        'photo_id' => $photo->id,
        'user_id' => $user->id,
        'value' => 1,
    ]);

    $photo->refresh();
    expect($photo->upvotes_count)->toBe(1);
    expect($photo->downvotes_count)->toBe(0);

    Event::assertDispatched(PhotoVoted::class, function ($event) use ($photo, $user) {
        return $event->photoId === $photo->id && $event->userId === $user->id && $event->value === 1;
    });
});

test('authenticated users can downvote a photo', function () {
    Event::fake(PhotoVoted::class);

    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.vote.store', $photo), [
        'value' => -1,
    ]);

    $response->assertRedirect();

    $photo->refresh();
    expect($photo->upvotes_count)->toBe(0);
    expect($photo->downvotes_count)->toBe(1);
});

test('voting with the same value again removes the vote', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    Vote::factory()->create([
        'photo_id' => $photo->id,
        'user_id' => $user->id,
        'value' => 1,
    ]);

    $response = $this->actingAs($user)->post(route('photos.vote.store', $photo), [
        'value' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Voto removido.');

    $this->assertDatabaseMissing('votes', [
        'photo_id' => $photo->id,
        'user_id' => $user->id,
    ]);

    $photo->refresh();
    expect($photo->upvotes_count)->toBe(0);
});

test('changing vote value updates the existing vote', function () {
    Event::fake(PhotoVoted::class);

    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    Vote::factory()->create([
        'photo_id' => $photo->id,
        'user_id' => $user->id,
        'value' => 1,
    ]);

    $response = $this->actingAs($user)->post(route('photos.vote.store', $photo), [
        'value' => -1,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('votes', [
        'photo_id' => $photo->id,
        'user_id' => $user->id,
        'value' => -1,
    ]);

    $photo->refresh();
    expect($photo->upvotes_count)->toBe(0);
    expect($photo->downvotes_count)->toBe(1);
});

test('vote value must be 1 or -1', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.vote.store', $photo), [
        'value' => 2,
    ]);

    $response->assertSessionHasErrors('value');
});
