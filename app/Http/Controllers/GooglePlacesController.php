<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GooglePlacesController extends Controller
{
    /**
     * Proxy to Google Places autocomplete API.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'min:2'],
        ]);

        $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
            'input' => $request->string('query')->value(),
            'key' => config('services.google_places.key'),
            'components' => 'country:cl',
            'language' => 'es',
        ]);

        return response()->json([
            'predictions' => $response->json('predictions', []),
        ]);
    }

    /**
     * Proxy to Google Places details API.
     */
    public function details(Request $request): JsonResponse
    {
        $request->validate([
            'place_id' => ['required', 'string'],
        ]);

        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $request->string('place_id')->value(),
            'key' => config('services.google_places.key'),
            'fields' => 'name,geometry,types,formatted_address,address_components',
            'language' => 'es',
        ]);

        return response()->json([
            'result' => $response->json('result'),
        ]);
    }
}
