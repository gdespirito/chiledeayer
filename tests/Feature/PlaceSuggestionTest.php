<?php

use App\Models\Photo;
use App\Models\PlaceSuggestion;
use App\Models\User;

it('allows authenticated user to submit a place suggestion', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $this->actingAs($user)
        ->post('/api/place-suggestions', [
            'photo_id' => $photo->id,
            'name' => 'Plaza de Armas',
            'city' => 'Santiago',
            'region' => 'Metropolitana',
            'notes' => 'Está frente a la catedral',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('place_suggestions', [
        'user_id' => $user->id,
        'photo_id' => $photo->id,
        'name' => 'Plaza de Armas',
        'city' => 'Santiago',
        'region' => 'Metropolitana',
        'country' => 'Chile',
        'notes' => 'Está frente a la catedral',
        'status' => 'pending',
    ]);
});

it('requires a name for place suggestion', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/api/place-suggestions', [
            'name' => '',
        ])
        ->assertSessionHasErrors('name');
});

it('requires authentication to submit a place suggestion', function () {
    $this->post('/api/place-suggestions', [
        'name' => 'Plaza de Armas',
    ])->assertRedirect('/login');
});

it('allows suggestion without photo_id', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/api/place-suggestions', [
            'name' => 'Cerro Santa Lucía',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('place_suggestions', [
        'user_id' => $user->id,
        'photo_id' => null,
        'name' => 'Cerro Santa Lucía',
        'country' => 'Chile',
    ]);
});

it('validates photo_id exists', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/api/place-suggestions', [
            'photo_id' => 99999,
            'name' => 'Test Place',
        ])
        ->assertSessionHasErrors('photo_id');
});

it('enforces max length on name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/api/place-suggestions', [
            'name' => str_repeat('a', 256),
        ])
        ->assertSessionHasErrors('name');
});

it('has relationships', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $suggestion = PlaceSuggestion::create([
        'user_id' => $user->id,
        'photo_id' => $photo->id,
        'name' => 'Test Place',
        'country' => 'Chile',
    ]);

    expect($suggestion->user)->toBeInstanceOf(User::class)
        ->and($suggestion->photo)->toBeInstanceOf(Photo::class);
});
