<?php

use App\Events\CommentCreated;
use App\Events\MetadataEdited;
use App\Events\PersonTagged;
use App\Events\PhotoUploaded;
use App\Events\PhotoVoted;
use App\Models\Badge;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\PointAction;
use App\Models\PointTransaction;
use App\Models\User;

beforeEach(function () {
    (new Database\Seeders\PointActionSeeder)->run();
    (new Database\Seeders\LevelSeeder)->run();
    (new Database\Seeders\BadgeSeeder)->run();
});

test('uploading a photo awards points', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();

    PhotoUploaded::dispatch($photo->id, $user->id);

    $user->refresh();

    $pointAction = PointAction::where('key', 'photo_uploaded')->first();

    expect(PointTransaction::where('user_id', $user->id)->where('point_action_id', $pointAction->id)->exists())->toBeTrue();
    expect($user->total_points)->toBeGreaterThanOrEqual($pointAction->points);
});

test('editing metadata awards points', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();

    MetadataEdited::dispatch($photo->id, $user->id, ['description' => 'updated']);

    $user->refresh();

    $pointAction = PointAction::where('key', 'metadata_edited')->first();

    expect(PointTransaction::where('user_id', $user->id)->where('point_action_id', $pointAction->id)->exists())->toBeTrue();
    expect($user->total_points)->toBeGreaterThanOrEqual($pointAction->points);
});

test('creating a comment awards points', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $comment = Comment::factory()->for($user)->for($photo)->create();

    CommentCreated::dispatch($comment->id, $photo->id, $user->id);

    $user->refresh();

    $pointAction = PointAction::where('key', 'comment_created')->first();

    expect(PointTransaction::where('user_id', $user->id)->where('point_action_id', $pointAction->id)->exists())->toBeTrue();
    expect($user->total_points)->toBeGreaterThanOrEqual($pointAction->points);
});

test('voting on a photo awards points', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    PhotoVoted::dispatch($photo->id, $user->id, 1);

    $user->refresh();

    $pointAction = PointAction::where('key', 'photo_voted')->first();

    expect(PointTransaction::where('user_id', $user->id)->where('point_action_id', $pointAction->id)->exists())->toBeTrue();
    expect($user->total_points)->toBeGreaterThanOrEqual($pointAction->points);
});

test('tagging a person awards points', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();

    PersonTagged::dispatch(1, $photo->id, $user->id);

    $user->refresh();

    $pointAction = PointAction::where('key', 'person_tagged')->first();

    expect(PointTransaction::where('user_id', $user->id)->where('point_action_id', $pointAction->id)->exists())->toBeTrue();
    expect($user->total_points)->toBeGreaterThanOrEqual($pointAction->points);
});

test('duplicate actions do not double award points', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();

    PhotoUploaded::dispatch($photo->id, $user->id);
    PhotoUploaded::dispatch($photo->id, $user->id);

    $user->refresh();

    $pointAction = PointAction::where('key', 'photo_uploaded')->first();

    $transactionCount = PointTransaction::where('user_id', $user->id)
        ->where('point_action_id', $pointAction->id)
        ->where('actionable_id', $photo->id)
        ->count();

    expect($transactionCount)->toBe(1);
    expect($user->total_points)->toBe($pointAction->points + Badge::where('key', 'first_upload')->first()->points_awarded);
});

test('user level updates correctly based on points', function () {
    $user = User::factory()->create(['total_points' => 0]);

    expect($user->currentLevel()->name)->toBe('Novato');

    $user->update(['total_points' => 55]);

    expect($user->currentLevel()->name)->toBe('Colaborador');

    $user->update(['total_points' => 250]);

    expect($user->currentLevel()->name)->toBe('Historiador');

    $user->update(['total_points' => 1500]);

    expect($user->currentLevel()->name)->toBe('Curador');
});

test('leaderboard shows users ranked by points', function () {
    $user1 = User::factory()->create(['total_points' => 100]);
    $user2 = User::factory()->create(['total_points' => 300]);
    $user3 = User::factory()->create(['total_points' => 50]);

    $response = $this->get(route('leaderboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Leaderboard')
        ->has('users.data', 3)
        ->where('users.data.0.user.id', $user2->id)
        ->where('users.data.1.user.id', $user1->id)
        ->where('users.data.2.user.id', $user3->id)
    );
});

test('badges are awarded when criteria are met', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();

    PhotoUploaded::dispatch($photo->id, $user->id);

    $user->refresh();

    expect($user->badges()->where('key', 'first_upload')->exists())->toBeTrue();
});

test('badge points are added to user total', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();

    PhotoUploaded::dispatch($photo->id, $user->id);

    $user->refresh();

    $uploadPoints = PointAction::where('key', 'photo_uploaded')->first()->points;
    $badgePoints = Badge::where('key', 'first_upload')->first()->points_awarded;

    expect($user->total_points)->toBe($uploadPoints + $badgePoints);
});

test('first comment badge is awarded on first comment', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $comment = Comment::factory()->for($user)->for($photo)->create();

    CommentCreated::dispatch($comment->id, $photo->id, $user->id);

    $user->refresh();

    expect($user->badges()->where('key', 'first_comment')->exists())->toBeTrue();
});

test('first edit badge is awarded on first metadata edit', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    MetadataEdited::dispatch($photo->id, $user->id, ['description' => 'updated']);

    $user->refresh();

    expect($user->badges()->where('key', 'first_edit')->exists())->toBeTrue();
});

test('first person tag badge is awarded on first tag', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();

    PersonTagged::dispatch(1, $photo->id, $user->id);

    $user->refresh();

    expect($user->badges()->where('key', 'first_person_tag')->exists())->toBeTrue();
});

test('points recalculate command updates user totals', function () {
    $user = User::factory()->create(['total_points' => 999]);
    $pointAction = PointAction::where('key', 'photo_uploaded')->first();
    $photo = Photo::factory()->for($user)->create();

    PointTransaction::create([
        'user_id' => $user->id,
        'point_action_id' => $pointAction->id,
        'points' => $pointAction->points,
        'actionable_type' => Photo::class,
        'actionable_id' => $photo->id,
    ]);

    $this->artisan('points:recalculate')
        ->assertSuccessful();

    $user->refresh();

    expect($user->total_points)->toBe($pointAction->points);
});

test('leaderboard excludes users with zero points', function () {
    User::factory()->create(['total_points' => 0]);
    $activeUser = User::factory()->create(['total_points' => 50]);

    $response = $this->get(route('leaderboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Leaderboard')
        ->has('users.data', 1)
        ->where('users.data.0.user.id', $activeUser->id)
    );
});
