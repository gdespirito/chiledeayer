<?php

use App\Models\Photo;
use App\Models\PhotoFile;
use App\Models\Place;
use App\Models\Tag;

test('guests can view place index', function () {
    Place::factory()->count(3)->create();

    $response = $this->get(route('places.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('places/Index')
        ->has('places.data', 3)
    );
});

test('guests can view place detail with photos', function () {
    $place = Place::factory()->create();
    Photo::factory()
        ->count(2)
        ->for($place)
        ->has(PhotoFile::factory(), 'files')
        ->create();

    $response = $this->get(route('places.show', $place->slug));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('places/Show')
        ->has('place.data', fn ($page) => $page
            ->where('id', $place->id)
            ->where('name', $place->name)
            ->where('slug', $place->slug)
            ->where('photos_count', 2)
            ->etc()
        )
        ->has('photos.data', 2)
    );
});

test('place search returns matching places', function () {
    Place::factory()->create(['name' => 'Santiago Centro', 'slug' => 'santiago-centro']);
    Place::factory()->create(['name' => 'Santiago Oriente', 'slug' => 'santiago-oriente']);
    Place::factory()->create(['name' => 'Valparaiso', 'slug' => 'valparaiso']);

    $response = $this->getJson(route('api.places.search', ['query' => 'Santiago']));

    $response->assertOk();
    $response->assertJsonCount(2);
    $response->assertJsonFragment(['name' => 'Santiago Centro']);
    $response->assertJsonFragment(['name' => 'Santiago Oriente']);
});

test('place search requires minimum 2 characters', function () {
    Place::factory()->create(['name' => 'Santiago', 'slug' => 'santiago']);

    $response = $this->getJson(route('api.places.search', ['query' => 'S']));

    $response->assertOk();
    $response->assertJsonCount(0);
});

test('guests can view tag index', function () {
    Tag::factory()->count(3)->create();

    $response = $this->get(route('tags.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('tags/Index')
        ->has('tags.data', 3)
    );
});

test('guests can view tag detail with photos', function () {
    $tag = Tag::factory()->create();
    $photos = Photo::factory()
        ->count(2)
        ->has(PhotoFile::factory(), 'files')
        ->create();

    $tag->photos()->attach($photos);

    $response = $this->get(route('tags.show', $tag->slug));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('tags/Show')
        ->has('tag.data', fn ($page) => $page
            ->where('id', $tag->id)
            ->where('name', $tag->name)
            ->where('slug', $tag->slug)
            ->where('photos_count', 2)
            ->etc()
        )
        ->has('photos.data', 2)
    );
});

test('tag search returns matching tags', function () {
    Tag::factory()->create(['name' => 'Santiago Antiguo', 'slug' => 'santiago-antiguo']);
    Tag::factory()->create(['name' => 'Santiago Moderno', 'slug' => 'santiago-moderno']);
    Tag::factory()->create(['name' => 'Valparaiso', 'slug' => 'valparaiso']);

    $response = $this->getJson(route('api.tags.search', ['query' => 'Santiago']));

    $response->assertOk();
    $response->assertJsonCount(2);
    $response->assertJsonFragment(['name' => 'Santiago Antiguo']);
    $response->assertJsonFragment(['name' => 'Santiago Moderno']);
});

test('tag search requires minimum 2 characters', function () {
    Tag::factory()->create(['name' => 'Santiago', 'slug' => 'santiago']);

    $response = $this->getJson(route('api.tags.search', ['query' => 'S']));

    $response->assertOk();
    $response->assertJsonCount(0);
});
