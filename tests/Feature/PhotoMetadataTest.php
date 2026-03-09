<?php

use App\Events\MetadataEdited;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('guests cannot edit photo metadata', function () {
    $photo = Photo::factory()->create();

    $response = $this->put(route('photos.metadata.update', $photo), [
        'title' => 'Updated description',
    ]);

    $response->assertRedirect(route('login'));
});

test('authenticated users can edit photo metadata', function () {
    Event::fake(MetadataEdited::class);

    $user = User::factory()->create();
    $photo = Photo::factory()->create(['title' => 'Original description']);

    $response = $this->actingAs($user)->put(route('photos.metadata.update', $photo), [
        'title' => 'Updated description',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $photo->refresh();
    expect($photo->title)->toBe('Updated description');

    Event::assertDispatched(MetadataEdited::class, function ($event) use ($photo, $user) {
        return $event->photoId === $photo->id && $event->userId === $user->id;
    });
});

test('metadata edit creates a revision record', function () {
    Event::fake(MetadataEdited::class);

    $user = User::factory()->create();
    $photo = Photo::factory()->create(['title' => 'Original']);

    $this->actingAs($user)->put(route('photos.metadata.update', $photo), [
        'title' => 'Edited',
    ]);

    $this->assertDatabaseHas('revisions', [
        'revisionable_type' => Photo::class,
        'revisionable_id' => $photo->id,
        'user_id' => $user->id,
    ]);

    $revision = $photo->revisions()->first();
    expect($revision->old_values['title'])->toBe('Original');
    expect($revision->new_values['title'])->toBe('Edited');
});

test('any authenticated user can edit metadata (wiki-style)', function () {
    Event::fake(MetadataEdited::class);

    $uploader = User::factory()->create();
    $editor = User::factory()->create();
    $photo = Photo::factory()->create([
        'user_id' => $uploader->id,
        'title' => 'Original',
    ]);

    $response = $this->actingAs($editor)->put(route('photos.metadata.update', $photo), [
        'title' => 'Edited by another user',
    ]);

    $response->assertRedirect();

    $photo->refresh();
    expect($photo->title)->toBe('Edited by another user');
});

test('partial updates only change provided fields', function () {
    Event::fake(MetadataEdited::class);

    $user = User::factory()->create();
    $photo = Photo::factory()->create([
        'title' => 'Original description',
        'source_credit' => 'Original source',
    ]);

    $this->actingAs($user)->put(route('photos.metadata.update', $photo), [
        'source_credit' => 'New source',
    ]);

    $photo->refresh();
    expect($photo->title)->toBe('Original description');
    expect($photo->source_credit)->toBe('New source');
});

test('title cannot exceed 255 characters', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->put(route('photos.metadata.update', $photo), [
        'title' => str_repeat('a', 256),
    ]);

    $response->assertSessionHasErrors('title');
});

test('no revision is created when no changes are provided', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $this->actingAs($user)->put(route('photos.metadata.update', $photo), []);

    expect($photo->revisions()->count())->toBe(0);
});
