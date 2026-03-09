<?php

use App\Models\Badge;
use App\Models\Level;
use App\Models\Photo;
use App\Models\PhotoFile;
use App\Models\User;

test('guests can view public user profile', function () {
    $user = User::factory()->create();

    $response = $this->get(route('users.show', $user));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('users/Show')
        ->has('user.data', fn ($page) => $page
            ->where('id', $user->id)
            ->where('name', $user->name)
            ->etc()
        )
    );
});

test('profile shows user photos', function () {
    $user = User::factory()->create();

    Photo::factory()
        ->count(3)
        ->for($user)
        ->has(PhotoFile::factory(), 'files')
        ->create();

    $response = $this->get(route('users.show', $user));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('users/Show')
        ->has('photos.data', 3)
        ->where('user.data.photos_count', 3)
    );
});

test('profile shows user points and level', function () {
    $level = Level::factory()->create(['min_points' => 0, 'name' => 'Novato', 'icon' => 'star']);

    $user = User::factory()->create(['total_points' => 100]);

    $response = $this->get(route('users.show', $user));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('users/Show')
        ->where('user.data.total_points', 100)
        ->has('user.data.level', fn ($page) => $page
            ->where('name', 'Novato')
            ->etc()
        )
    );
});

test('profile shows user badges', function () {
    $user = User::factory()->create();
    $badge = Badge::factory()->create(['name' => 'Primera Foto']);

    $user->badges()->attach($badge->id, ['awarded_at' => now()]);

    $response = $this->get(route('users.show', $user));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('users/Show')
        ->has('user.data.badges', 1)
        ->where('user.data.badges.0.name', 'Primera Foto')
    );
});
