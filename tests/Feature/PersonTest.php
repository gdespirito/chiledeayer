<?php

use App\Events\PersonTagged;
use App\Models\Person;
use App\Models\Photo;
use App\Models\PhotoFile;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('guests can view person index', function () {
    Person::factory()->count(3)->create();

    $response = $this->get(route('persons.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('persons/Index')
        ->has('persons.data', 3)
    );
});

test('guests can view person detail', function () {
    $person = Person::factory()->create();
    $photo = Photo::factory()
        ->has(PhotoFile::factory(), 'files')
        ->create();
    $person->photos()->attach($photo, ['x' => 50.00, 'y' => 30.00]);

    $response = $this->get(route('persons.show', $person));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('persons/Show')
        ->has('person.data', fn ($page) => $page
            ->where('id', $person->id)
            ->where('name', $person->name)
            ->etc()
        )
        ->has('photos.data', 1)
    );
});

test('guests cannot tag persons', function () {
    $photo = Photo::factory()->create();

    $response = $this->post(route('photos.persons.store', $photo));

    $response->assertRedirect(route('login'));
});

test('authenticated users can tag a new person to a photo', function () {
    Event::fake([PersonTagged::class]);

    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.persons.store', $photo), [
        'person_name' => 'Arturo Prat',
        'x' => 45.50,
        'y' => 30.25,
        'label' => 'Capitán Prat',
    ]);

    $response->assertRedirect();

    $person = Person::where('name', 'Arturo Prat')->first();
    expect($person)->not->toBeNull();
    expect($person->type)->toBe('unknown');

    expect($photo->persons)->toHaveCount(1);
    expect((float) $photo->persons->first()->pivot->x)->toBe(45.50);
    expect((float) $photo->persons->first()->pivot->y)->toBe(30.25);
    expect($photo->persons->first()->pivot->label)->toBe('Capitán Prat');

    Event::assertDispatched(PersonTagged::class, function ($event) use ($person, $photo, $user) {
        return $event->personId === $person->id
            && $event->photoId === $photo->id
            && $event->userId === $user->id;
    });
});

test('authenticated users can tag an existing person to a photo', function () {
    Event::fake([PersonTagged::class]);

    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $person = Person::factory()->create(['name' => 'Bernardo O\'Higgins']);

    $response = $this->actingAs($user)->post(route('photos.persons.store', $photo), [
        'person_id' => $person->id,
        'x' => 60.00,
        'y' => 40.00,
    ]);

    $response->assertRedirect();

    $photo->refresh();
    expect($photo->persons)->toHaveCount(1);
    expect($photo->persons->first()->id)->toBe($person->id);

    Event::assertDispatched(PersonTagged::class);
});

test('authenticated users can untag a person from a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $person = Person::factory()->create();
    $photo->persons()->attach($person, ['x' => 50.00, 'y' => 50.00]);

    $response = $this->actingAs($user)->delete(route('photos.persons.destroy', [$photo, $person]));

    $response->assertRedirect();

    $photo->refresh();
    expect($photo->persons)->toHaveCount(0);
});

test('person tag validates required fields', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.persons.store', $photo), []);

    $response->assertSessionHasErrors(['person_name']);
});

test('person tag includes coordinates', function () {
    Event::fake([PersonTagged::class]);

    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->post(route('photos.persons.store', $photo), [
        'person_name' => 'Manuel Rodriguez',
        'x' => 25.75,
        'y' => 80.50,
    ]);

    $response->assertRedirect();

    $photo->refresh();
    $tagged = $photo->persons->first();
    expect($tagged)->not->toBeNull();
    expect((float) $tagged->pivot->x)->toBe(25.75);
    expect((float) $tagged->pivot->y)->toBe(80.50);
});
