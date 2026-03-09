<?php

use App\Models\Photo;
use App\Models\PhotoFile;
use App\Models\Place;
use App\Models\Tag;

test('search page renders successfully', function () {
    $response = $this->get(route('search'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Search'));
});

test('search page returns matching photos', function () {
    Photo::factory()
        ->has(PhotoFile::factory(), 'files')
        ->create(['description' => 'Plaza de Armas de Santiago']);

    Photo::factory()
        ->has(PhotoFile::factory(), 'files')
        ->create(['description' => 'Playa de Vina del Mar']);

    $response = $this->get(route('search', ['q' => 'Santiago']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Search')
        ->has('filters')
        ->has('facets')
    );
});

test('search page includes facets', function () {
    $place = Place::factory()->create();
    $tag = Tag::factory()->create();

    Photo::factory()
        ->has(PhotoFile::factory(), 'files')
        ->hasAttached($tag)
        ->create(['place_id' => $place->id]);

    $response = $this->get(route('search'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Search')
        ->has('facets.places', 1)
        ->has('facets.tags', 1)
    );
});

test('search page preserves filter params', function () {
    $response = $this->get(route('search', [
        'q' => 'test',
        'decade' => '1920',
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Search')
        ->where('filters.q', 'test')
        ->where('filters.decade', '1920')
    );
});

test('search page handles empty results', function () {
    $response = $this->get(route('search', ['q' => 'nonexistent_query_12345']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Search')
        ->has('photos.data', 0)
    );
});
