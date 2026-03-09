<?php

use App\Models\Photo;
use App\Models\PhotoFile;

test('home page renders successfully', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Home'));
});

test('home page shows latest photos', function () {
    $photos = Photo::factory()
        ->count(3)
        ->has(PhotoFile::factory(), 'files')
        ->create();

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Home')
        ->has('latest.data', 3)
    );
});

test('home page shows popular photos', function () {
    Photo::factory()->create(['upvotes_count' => 10, 'downvotes_count' => 0]);
    Photo::factory()->create(['upvotes_count' => 5, 'downvotes_count' => 0]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Home')
        ->has('popular.data', 2)
    );
});

test('home page shows photo of the day when featured photo exists', function () {
    Photo::factory()
        ->has(PhotoFile::factory(), 'files')
        ->create(['featured_at' => now()]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Home')
        ->has('photoOfTheDay.data')
    );
});

test('home page shows needs help section', function () {
    Photo::factory()->count(3)->create();

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Home')
        ->has('needsHelp.data', 3)
    );
});

test('home page handles empty database gracefully', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Home')
        ->where('photoOfTheDay', null)
        ->has('latest.data', 0)
        ->has('popular.data', 0)
        ->has('needsHelp.data', 0)
    );
});
