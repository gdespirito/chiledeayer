<?php

use App\Models\Comment;
use App\Models\Photo;
use App\Models\User;

test('non-admin cannot delete photos via admin route', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->deleteJson(route('admin.photos.destroy', $photo));

    $response->assertForbidden();
});

test('admin can soft-delete a photo', function () {
    $admin = User::factory()->admin()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($admin)->deleteJson(route('admin.photos.destroy', $photo));

    $response->assertOk();
    $response->assertJsonPath('message', 'Foto eliminada.');

    expect($photo->fresh()->trashed())->toBeTrue();
});

test('admin can delete a comment', function () {
    $admin = User::factory()->admin()->create();
    $comment = Comment::factory()->create();

    $response = $this->actingAs($admin)->deleteJson(route('admin.comments.destroy', $comment));

    $response->assertOk();
    $response->assertJsonPath('message', 'Comentario eliminado.');

    expect(Comment::find($comment->id))->toBeNull();
});

test('admin can ban a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $photos = Photo::factory()->count(3)->for($user)->create();

    $response = $this->actingAs($admin)->postJson(route('admin.users.ban', $user));

    $response->assertOk();
    $response->assertJsonPath('message', 'Usuario suspendido.');

    expect($user->fresh()->is_banned)->toBeTrue();
    expect(Photo::where('user_id', $user->id)->count())->toBe(0);
    expect(Photo::withTrashed()->where('user_id', $user->id)->count())->toBe(3);
});

test('admin can unban a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->banned()->create();

    $response = $this->actingAs($admin)->postJson(route('admin.users.unban', $user));

    $response->assertOk();
    $response->assertJsonPath('message', 'Usuario reactivado.');

    expect($user->fresh()->is_banned)->toBeFalse();
});

test('banned user gets 403 on authenticated routes', function () {
    $user = User::factory()->banned()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertForbidden();
});

test('banned user gets 403 on api routes with token', function () {
    $user = User::factory()->banned()->create();
    $token = $user->createToken('test');

    $response = $this->withToken($token->plainTextToken)->postJson('/api/v1/photos', [
        'description' => 'Test',
        'year_from' => 1920,
        'date_precision' => 'year',
    ]);

    $response->assertForbidden();
});

test('non-admin cannot ban users', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('admin.users.ban', $target));

    $response->assertForbidden();
});
