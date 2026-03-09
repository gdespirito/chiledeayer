<?php

use App\Models\Place;
use App\Models\User;

it('creates a place from google places data', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/places', [
            'google_place_id' => 'ChIJL68sLWXFYpYRGqb3B5OOOOA',
            'name' => 'Plaza de Armas de Santiago',
            'latitude' => -33.4372,
            'longitude' => -70.6506,
            'city' => 'Santiago',
            'region' => 'Región Metropolitana',
            'country' => 'Chile',
        ])
        ->assertSuccessful()
        ->assertJsonPath('place.name', 'Plaza de Armas de Santiago');

    $this->assertDatabaseHas('places', [
        'google_place_id' => 'ChIJL68sLWXFYpYRGqb3B5OOOOA',
        'name' => 'Plaza de Armas de Santiago',
        'verified' => false,
    ]);
});

it('reuses existing place by google_place_id', function () {
    $user = User::factory()->create();
    $existing = Place::factory()->create([
        'google_place_id' => 'ChIJL68sLWXFYpYRGqb3B5OOOOA',
        'name' => 'Plaza de Armas',
        'verified' => true,
    ]);

    $this->actingAs($user)
        ->postJson('/api/places', [
            'google_place_id' => 'ChIJL68sLWXFYpYRGqb3B5OOOOA',
            'name' => 'Plaza de Armas de Santiago',
            'latitude' => -33.4372,
            'longitude' => -70.6506,
        ])
        ->assertSuccessful()
        ->assertJsonPath('place.id', $existing->id);

    expect(Place::where('google_place_id', 'ChIJL68sLWXFYpYRGqb3B5OOOOA')->count())->toBe(1);
});

it('requires google_place_id', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/places', [
            'name' => 'Test Place',
            'latitude' => -33.4,
            'longitude' => -70.6,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('google_place_id');
});

it('requires authentication', function () {
    $this->postJson('/api/places', [
        'google_place_id' => 'test',
        'name' => 'Test',
        'latitude' => -33.4,
        'longitude' => -70.6,
    ])->assertUnauthorized();
});
