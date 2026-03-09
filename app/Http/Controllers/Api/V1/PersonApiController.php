<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Models\Person;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PersonApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PersonResource::collection(
            Person::query()
                ->withCount('photos')
                ->orderBy('name')
                ->paginate(24)
        );
    }

    public function show(Person $person): PersonResource
    {
        $person->loadCount('photos');

        return new PersonResource($person);
    }
}
