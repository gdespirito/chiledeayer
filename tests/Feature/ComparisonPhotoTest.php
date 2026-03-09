<?php

use App\Jobs\ProcessComparisonUpload;
use App\Models\ComparisonPhoto;
use App\Models\Photo;
use App\Models\PhotoFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;

test('guests cannot upload comparison photo', function () {
    $photo = Photo::factory()->create();

    $response = $this->post(route('photos.comparisons.store', $photo));

    $response->assertRedirect(route('login'));
});

test('authenticated users can upload a comparison photo', function () {
    Queue::fake();

    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.comparisons.store', $photo), [
        'photo' => UploadedFile::fake()->image('now.jpg', 1200, 800),
        'description' => 'La misma esquina hoy',
        'taken_at' => '2026-03-01',
    ]);

    $comparison = ComparisonPhoto::first();

    expect($comparison)->not->toBeNull();
    expect($comparison->photo_id)->toBe($photo->id);
    expect($comparison->user_id)->toBe($user->id);
    expect($comparison->description)->toBe('La misma esquina hoy');
    expect($comparison->taken_at->toDateString())->toBe('2026-03-01');
    expect($comparison->original_path)->not->toBeNull();

    Queue::assertPushed(ProcessComparisonUpload::class, function ($job) use ($comparison) {
        return $job->comparisonPhoto->id === $comparison->id;
    });

    $response->assertRedirect(route('photos.show', $photo));
});

test('comparison photo validates image file', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.comparisons.store', $photo), [
        'photo' => UploadedFile::fake()->create('document.pdf', 500, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors(['photo']);
});

test('comparison photo shows on photo detail', function () {
    $photo = Photo::factory()
        ->has(PhotoFile::factory(), 'files')
        ->create();

    $comparison = ComparisonPhoto::factory()
        ->for($photo)
        ->processed()
        ->create();

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('photos/Show')
        ->has('photo.data.comparisons', 1)
        ->where('photo.data.comparisons.0.id', $comparison->id)
    );
});

test('duplicate comparison per user per photo is rejected', function () {
    Queue::fake();

    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    ComparisonPhoto::factory()
        ->for($photo)
        ->for($user)
        ->create();

    $response = $this->actingAs($user)->post(route('photos.comparisons.store', $photo), [
        'photo' => UploadedFile::fake()->image('now2.jpg', 1200, 800),
    ]);

    expect(ComparisonPhoto::where('photo_id', $photo->id)->where('user_id', $user->id)->count())->toBe(1);
});
