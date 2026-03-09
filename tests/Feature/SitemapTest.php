<?php

use App\Models\Person;
use App\Models\Photo;
use App\Models\Place;
use App\Models\Tag;

test('sitemap returns valid xml with correct content type', function () {
    $response = $this->get(route('sitemap'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');
    $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
    $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
});

test('sitemap includes static pages', function () {
    $response = $this->get(route('sitemap'));

    $baseUrl = config('app.url');

    $response->assertSee("<loc>{$baseUrl}/</loc>", false);
    $response->assertSee("<loc>{$baseUrl}/photos</loc>", false);
    $response->assertSee("<loc>{$baseUrl}/places</loc>", false);
    $response->assertSee("<loc>{$baseUrl}/tags</loc>", false);
    $response->assertSee("<loc>{$baseUrl}/persons</loc>", false);
    $response->assertSee("<loc>{$baseUrl}/map</loc>", false);
    $response->assertSee("<loc>{$baseUrl}/leaderboard</loc>", false);
});

test('sitemap includes individual photos', function () {
    $photo = Photo::factory()->create();

    $response = $this->get(route('sitemap'));

    $baseUrl = config('app.url');
    $response->assertSee("<loc>{$baseUrl}/photos/{$photo->id}</loc>", false);
    $response->assertSee('<changefreq>weekly</changefreq>', false);
    $response->assertSee('<priority>0.8</priority>', false);
});

test('sitemap includes individual places', function () {
    $place = Place::factory()->create();

    $response = $this->get(route('sitemap'));

    $baseUrl = config('app.url');
    $response->assertSee("<loc>{$baseUrl}/places/{$place->slug}</loc>", false);
});

test('sitemap includes individual tags', function () {
    $tag = Tag::factory()->create();

    $response = $this->get(route('sitemap'));

    $baseUrl = config('app.url');
    $response->assertSee("<loc>{$baseUrl}/tags/{$tag->slug}</loc>", false);
});

test('sitemap includes individual persons', function () {
    $person = Person::factory()->create();

    $response = $this->get(route('sitemap'));

    $baseUrl = config('app.url');
    $response->assertSee("<loc>{$baseUrl}/persons/{$person->slug}</loc>", false);
});

test('sitemap has correct priorities', function () {
    $response = $this->get(route('sitemap'));

    $content = $response->getContent();

    // Home page has priority 1.0
    expect($content)->toContain('<priority>1.0</priority>');

    // Index pages have priority 0.9
    $matches = [];
    preg_match_all('/<priority>0\.9<\/priority>/', $content, $matches);
    expect(count($matches[0]))->toBe(6); // photos, places, tags, persons, map, leaderboard
});

test('sitemap excludes soft-deleted photos', function () {
    $photo = Photo::factory()->create();
    $photo->delete();

    $response = $this->get(route('sitemap'));

    $baseUrl = config('app.url');
    $response->assertDontSee("<loc>{$baseUrl}/photos/{$photo->id}</loc>", false);
});
