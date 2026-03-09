<?php

use App\Jobs\ProcessPhotoUpload;
use App\Models\Photo;
use App\Models\PhotoFile;
use App\Models\Place;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

test('guests can view photo index', function () {
    $response = $this->get(route('photos.index'));

    $response->assertOk();
});

test('guests can view photo detail', function () {
    Storage::fake('s3');

    $photo = Photo::factory()
        ->has(PhotoFile::factory(), 'files')
        ->create();

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('photos/Show')
        ->has('photo.data', fn ($page) => $page
            ->where('id', $photo->id)
            ->where('title', $photo->title)
            ->etc()
        )
    );
});

test('guests cannot access create page', function () {
    $response = $this->get(route('photos.create'));

    $response->assertRedirect(route('login'));
});

test('guests cannot upload photos', function () {
    $response = $this->post(route('photos.store'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can access create page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('photos.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('photos/Create'));
});

test('authenticated users can upload a photo', function () {
    Storage::fake('s3');
    Queue::fake();

    $user = User::factory()->create();
    $place = Place::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.store'), [
        'photo' => UploadedFile::fake()->image('photo.jpg', 1200, 800),
        'title' => 'Una foto historica de Santiago',
        'year_from' => 1920,
        'year_to' => 1925,
        'date_precision' => 'circa',
        'place_id' => $place->id,
        'source_credit' => 'Archivo Nacional',
        'tags' => ['Santiago Antiguo', 'Centro'],
    ]);

    $photo = Photo::first();

    expect($photo)->not->toBeNull();
    expect($photo->title)->toBe('Una foto historica de Santiago');
    expect($photo->year_from)->toBe(1920);
    expect($photo->year_to)->toBe(1925);
    expect($photo->date_precision)->toBe('circa');
    expect($photo->user_id)->toBe($user->id);
    expect($photo->place_id)->toBe($place->id);
    expect($photo->source_credit)->toBe('Archivo Nacional');

    $storedFiles = Storage::disk('s3')->allFiles('photos/'.$user->id);
    expect($storedFiles)->toHaveCount(1);

    Queue::assertPushed(ProcessPhotoUpload::class, function ($job) use ($photo) {
        return $job->photo->id === $photo->id;
    });

    $response->assertRedirect(route('photos.show', $photo));
});

test('photo upload validates required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.store'), []);

    $response->assertSessionHasErrors(['photo', 'title', 'year_from', 'date_precision']);
});

test('photo upload validates year range', function () {
    Storage::fake('s3');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.store'), [
        'photo' => UploadedFile::fake()->image('photo.jpg', 1200, 800),
        'title' => 'Test photo',
        'year_from' => 1950,
        'year_to' => 1940,
        'date_precision' => 'year',
    ]);

    $response->assertSessionHasErrors(['year_to']);
});

test('photo upload validates image file', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.store'), [
        'photo' => UploadedFile::fake()->create('document.pdf', 500, 'application/pdf'),
        'title' => 'Test photo',
        'year_from' => 1950,
        'date_precision' => 'year',
    ]);

    $response->assertSessionHasErrors(['photo']);
});

test('tags are created and attached on upload', function () {
    Storage::fake('s3');
    Queue::fake();

    $user = User::factory()->create();

    $this->actingAs($user)->post(route('photos.store'), [
        'photo' => UploadedFile::fake()->image('photo.jpg', 1200, 800),
        'title' => 'Foto con tags',
        'year_from' => 1900,
        'date_precision' => 'decade',
        'tags' => ['Plaza de Armas', 'Centro Historico'],
    ]);

    $photo = Photo::first();

    expect(Tag::count())->toBe(2);
    expect($photo->tags)->toHaveCount(2);
    expect(Tag::where('slug', 'plaza-de-armas')->exists())->toBeTrue();
    expect(Tag::where('slug', 'centro-historico')->exists())->toBeTrue();
});

test('photo index shows latest photos first', function () {
    $photos = Photo::factory()
        ->count(3)
        ->sequence(
            ['created_at' => now()->subDays(2), 'title' => 'Oldest'],
            ['created_at' => now()->subDay(), 'title' => 'Middle'],
            ['created_at' => now(), 'title' => 'Newest'],
        )
        ->create();

    $response = $this->get(route('photos.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('photos/Index')
        ->has('photos.data', 3)
        ->where('photos.data.0.title', 'Newest')
        ->where('photos.data.1.title', 'Middle')
        ->where('photos.data.2.title', 'Oldest')
    );
});

test('photo show includes related data', function () {
    Storage::fake('s3');

    $place = Place::factory()->create();
    $user = User::factory()->create();
    $tags = Tag::factory()->count(2)->create();

    $photo = Photo::factory()
        ->for($user)
        ->for($place)
        ->has(PhotoFile::factory(), 'files')
        ->create();

    $photo->tags()->attach($tags);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('photos/Show')
        ->has('photo.data', fn ($page) => $page
            ->where('id', $photo->id)
            ->has('user')
            ->has('place')
            ->has('files', 1)
            ->has('tags', 2)
            ->etc()
        )
    );
});
