<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlaceRequest;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PlaceStoreController extends Controller
{
    public function __invoke(StorePlaceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $place = Place::firstOrCreate(
            ['google_place_id' => $validated['google_place_id']],
            [
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'city' => $validated['city'] ?? null,
                'region' => $validated['region'] ?? null,
                'country' => $validated['country'] ?? 'Chile',
                'type' => 'precise',
                'verified' => false,
            ],
        );

        return response()->json(['place' => $place]);
    }
}
