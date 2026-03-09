<?php

use App\Models\Photo;
use App\Models\PhotoFile;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;

test('public api returns paginated photos', function () {
    Photo::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/photos');

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [['id', 'title', 'year_from']],
        'links',
        'meta',
    ]);
    $response->assertJsonCount(3, 'data');
});

test('public api returns single photo', function () {
    $photo = Photo::factory()
        ->has(PhotoFile::factory(), 'files')
        ->create();

    $response = $this->getJson("/api/v1/photos/{$photo->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $photo->id);
    $response->assertJsonPath('data.title', $photo->title);
});

test('public api returns paginated places', function () {
    Place::factory()->count(2)->create();

    $response = $this->getJson('/api/v1/places');

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('public api returns single place by slug', function () {
    $place = Place::factory()->create(['slug' => 'plaza-de-armas']);

    $response = $this->getJson('/api/v1/places/plaza-de-armas');

    $response->assertOk();
    $response->assertJsonPath('data.slug', 'plaza-de-armas');
});

test('authenticated api can upload photo with sanctum token', function () {
    Queue::fake();

    $user = User::factory()->create();
    $place = Place::factory()->create();
    $token = $user->createToken('test-token');

    $response = $this->withToken($token->plainTextToken)->postJson('/api/v1/photos', [
        'photo' => UploadedFile::fake()->image('photo.jpg', 1200, 800),
        'title' => 'Test API upload',
        'year_from' => 1920,
        'date_precision' => 'year',
        'place_id' => $place->id,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.title', 'Test API upload');

    expect(Photo::count())->toBe(1);
});

test('unauthenticated api cannot upload photo', function () {
    $response = $this->postJson('/api/v1/photos', [
        'title' => 'Test',
        'year_from' => 1920,
        'date_precision' => 'year',
    ]);

    $response->assertUnauthorized();
});

test('api token management works', function () {
    $user = User::factory()->create();

    $createResponse = $this->actingAs($user)->postJson(route('api.tokens.store'), [
        'name' => 'My API Token',
    ]);

    $createResponse->assertCreated();
    $createResponse->assertJsonStructure(['token', 'message']);

    $listResponse = $this->actingAs($user)->getJson(route('api.tokens.index'));
    $listResponse->assertOk();
    $listResponse->assertJsonCount(1, 'tokens');

    $tokenId = $listResponse->json('tokens.0.id');

    $deleteResponse = $this->actingAs($user)->deleteJson(route('api.tokens.destroy', $tokenId));
    $deleteResponse->assertOk();

    $listAfterDelete = $this->actingAs($user)->getJson(route('api.tokens.index'));
    $listAfterDelete->assertJsonCount(0, 'tokens');
});

test('user cannot revoke another users token', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $token = $user1->createToken('token');

    $response = $this->actingAs($user2)->deleteJson(route('api.tokens.destroy', $token->accessToken->id));

    $response->assertForbidden();
});
