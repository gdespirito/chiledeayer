<?php

namespace App\Http\Controllers;

use App\Http\Resources\PersonResource;
use App\Http\Resources\PhotoResource;
use App\Models\Person;
use Inertia\Inertia;
use Inertia\Response;

class PersonController extends Controller
{
    /**
     * Display a listing of persons.
     */
    public function index(): Response
    {
        return Inertia::render('persons/Index', [
            'persons' => PersonResource::collection(
                Person::query()
                    ->withCount('photos')
                    ->orderByRaw("CASE WHEN type = 'public' THEN 0 ELSE 1 END")
                    ->orderBy('name')
                    ->paginate(24)
            ),
        ]);
    }

    /**
     * Display the specified person.
     */
    public function show(Person $person): Response
    {
        $person->loadCount('photos');

        return Inertia::render('persons/Show', [
            'person' => new PersonResource($person),
            'photos' => PhotoResource::collection(
                $person->photos()
                    ->with(['files', 'user', 'place', 'tags'])
                    ->latest()
                    ->paginate(24)
            ),
        ]);
    }
}
