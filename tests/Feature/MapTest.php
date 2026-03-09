<?php

use App\Models\Photo;
use App\Models\PhotoFile;
use App\Models\Place;

test('map page renders', function () {
    $response = $this->get(route('map'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Map'));
});

test('map photos endpoint returns photos within bounds', function () {
    $placeInside = Place::factory()->create([
        'latitude' => -33.45,
        'longitude' => -70.65,
    ]);

    $placeOutside = Place::factory()->create([
        'latitude' => 40.0,
        'longitude' => -3.7,
    ]);

    Photo::factory()
        ->for($placeInside)
        ->has(PhotoFile::factory()->thumb(), 'files')
        ->create(['title' => 'Santiago antiguo']);

    Photo::factory()
        ->for($placeOutside)
        ->has(PhotoFile::factory()->thumb(), 'files')
        ->create(['title' => 'Madrid foto']);

    $response = $this->getJson(route('api.map.photos', [
        'north' => -30.0,
        'south' => -40.0,
        'east' => -65.0,
        'west' => -75.0,
    ]));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment(['title' => 'Santiago antiguo']);
    $response->assertJsonMissing(['title' => 'Madrid foto']);
});

test('map photos endpoint filters by year range', function () {
    $place = Place::factory()->create([
        'latitude' => -33.45,
        'longitude' => -70.65,
    ]);

    Photo::factory()
        ->for($place)
        ->create(['year_from' => 1920, 'year_to' => 1930]);

    Photo::factory()
        ->for($place)
        ->create(['year_from' => 1980, 'year_to' => 1990]);

    $response = $this->getJson(route('api.map.photos', [
        'north' => -30.0,
        'south' => -40.0,
        'east' => -65.0,
        'west' => -75.0,
        'year_from' => 1900,
        'year_to' => 1950,
    ]));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment(['year_from' => 1920]);
});

test('map photos endpoint limits results to 500', function () {
    $place = Place::factory()->create([
        'latitude' => -33.45,
        'longitude' => -70.65,
    ]);

    Photo::factory()
        ->count(510)
        ->for($place)
        ->create();

    $response = $this->getJson(route('api.map.photos', [
        'north' => -30.0,
        'south' => -40.0,
        'east' => -65.0,
        'west' => -75.0,
    ]));

    $response->assertOk();
    $response->assertJsonCount(500);
});

test('map photos endpoint validates required bounds', function () {
    $response = $this->getJson(route('api.map.photos'));

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['north', 'south', 'east', 'west']);
});

test('map photos endpoint excludes photos without a place', function () {
    $placeInBounds = Place::factory()->create([
        'latitude' => -33.45,
        'longitude' => -70.65,
    ]);

    Photo::factory()
        ->for($placeInBounds)
        ->create(['title' => 'Geolocated photo']);

    Photo::factory()
        ->withoutPlace()
        ->create(['title' => 'No place photo']);

    $response = $this->getJson(route('api.map.photos', [
        'north' => -30.0,
        'south' => -40.0,
        'east' => -65.0,
        'west' => -75.0,
    ]));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment(['title' => 'Geolocated photo']);
});
