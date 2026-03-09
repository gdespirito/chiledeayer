<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlaceApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PlaceResource::collection(
            Place::query()
                ->withCount('photos')
                ->orderBy('name')
                ->paginate(24)
        );
    }

    public function show(Place $place): PlaceResource
    {
        $place->loadCount('photos');

        return new PlaceResource($place);
    }
}
