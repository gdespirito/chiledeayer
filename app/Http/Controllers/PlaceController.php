<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhotoResource;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlaceController extends Controller
{
    /**
     * Display a listing of places.
     */
    public function index(): Response
    {
        return Inertia::render('places/Index', [
            'places' => PlaceResource::collection(
                Place::query()
                    ->withCount('photos')
                    ->orderBy('name')
                    ->paginate(24)
            ),
        ]);
    }

    /**
     * Display the specified place with its photos.
     */
    public function show(Place $place): Response
    {
        return Inertia::render('places/Show', [
            'place' => new PlaceResource($place->loadCount('photos')),
            'photos' => PhotoResource::collection(
                $place->photos()
                    ->with(['files', 'user', 'place', 'tags'])
                    ->latest()
                    ->paginate(24)
            ),
        ]);
    }

    /**
     * Search places by name for autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->string('query')->trim()->value();

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $places = Place::query()
            ->where('name', 'like', "%{$query}%")
            ->withCount('photos')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json(PlaceResource::collection($places));
    }
}
