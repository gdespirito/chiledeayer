<?php

use App\Jobs\RecordPhotoVisit;
use App\Models\Photo;
use App\Models\PhotoFile;
use App\Models\PhotoVisit;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('viewing a photo dispatches RecordPhotoVisit job', function () {
    Queue::fake();

    $photo = Photo::factory()
        ->has(PhotoFile::factory(), 'files')
        ->create();

    $this->get(route('photos.show', $photo));

    Queue::assertPushed(RecordPhotoVisit::class, function ($job) use ($photo) {
        return $job->photoId === $photo->id && $job->userId === null;
    });
});

test('viewing a photo as authenticated user includes user id', function () {
    Queue::fake();

    $user = User::factory()->create();
    $photo = Photo::factory()
        ->has(PhotoFile::factory(), 'files')
        ->create();

    $this->actingAs($user)->get(route('photos.show', $photo));

    Queue::assertPushed(RecordPhotoVisit::class, function ($job) use ($photo, $user) {
        return $job->photoId === $photo->id && $job->userId === $user->id;
    });
});

test('RecordPhotoVisit job creates a visit record and increments counter', function () {
    $photo = Photo::factory()->create();

    $job = new RecordPhotoVisit($photo->id, null, [
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
        'referer' => 'https://google.com',
        'timezone' => 'America/Santiago',
    ]);

    $job->handle();

    expect(PhotoVisit::count())->toBe(1);

    $visit = PhotoVisit::first();
    expect($visit->photo_id)->toBe($photo->id);
    expect($visit->ip_address)->toBe('192.168.1.1');
    expect($visit->timezone)->toBe('America/Santiago');
    expect($visit->referer)->toBe('https://google.com');
    expect($visit->is_bot)->toBeFalse();

    $photo->refresh();
    expect($photo->visits_count)->toBe(1);
});

test('bot visits are recorded but do not increment counter', function () {
    $photo = Photo::factory()->create();

    $job = new RecordPhotoVisit($photo->id, null, [
        'ip_address' => '66.249.66.1',
        'user_agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        'referer' => null,
        'timezone' => null,
    ]);

    $job->handle();

    $visit = PhotoVisit::first();
    expect($visit->is_bot)->toBeTrue();

    $photo->refresh();
    expect($photo->visits_count)->toBe(0);
});

test('visits count is included in photo resource', function () {
    $photo = Photo::factory()
        ->has(PhotoFile::factory(), 'files')
        ->create(['visits_count' => 42]);

    $response = $this->getJson("/api/v1/photos/{$photo->id}");

    $response->assertOk();
    $response->assertJsonPath('data.visits_count', 42);
});
