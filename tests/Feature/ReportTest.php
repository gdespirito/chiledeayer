<?php

use App\Models\Photo;
use App\Models\User;

test('guests cannot report photos', function () {
    $photo = Photo::factory()->create();

    $response = $this->post(route('photos.report.store', $photo), [
        'reason' => 'This is a duplicate',
    ]);

    $response->assertRedirect(route('login'));
});

test('authenticated users can report a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.report.store', $photo), [
        'reason' => 'This is inappropriate content',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('reports', [
        'user_id' => $user->id,
        'photo_id' => $photo->id,
        'reason' => 'This is inappropriate content',
        'status' => 'pending',
    ]);
});

test('report can include a duplicate photo reference', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $duplicate = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.report.store', $photo), [
        'reason' => 'Duplicate of another photo',
        'duplicate_of_id' => $duplicate->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('reports', [
        'photo_id' => $photo->id,
        'duplicate_of_id' => $duplicate->id,
    ]);
});

test('report reason is required', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.report.store', $photo), [
        'reason' => '',
    ]);

    $response->assertSessionHasErrors('reason');
});

test('report reason cannot exceed 2000 characters', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.report.store', $photo), [
        'reason' => str_repeat('a', 2001),
    ]);

    $response->assertSessionHasErrors('reason');
});

test('duplicate_of_id must reference an existing photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.report.store', $photo), [
        'reason' => 'Duplicate',
        'duplicate_of_id' => 99999,
    ]);

    $response->assertSessionHasErrors('duplicate_of_id');
});
