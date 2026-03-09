<?php

namespace App\Http\Controllers;

use App\Services\GooglePlacesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GooglePlacesController extends Controller
{
    public function __construct(
        private GooglePlacesService $places,
    ) {}

    /**
     * Proxy to Google Places Autocomplete.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'min:2'],
        ]);

        return response()->json([
            'predictions' => $this->places->autocomplete($request->string('query')->value()),
        ]);
    }

    /**
     * Proxy to Google Places Details.
     */
    public function details(Request $request): JsonResponse
    {
        $request->validate([
            'place_id' => ['required', 'string'],
        ]);

        return response()->json([
            'result' => $this->places->details($request->string('place_id')->value()),
        ]);
    }
}
