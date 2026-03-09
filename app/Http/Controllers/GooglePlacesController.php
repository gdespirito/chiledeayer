<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GooglePlacesController extends Controller
{
    /**
     * Proxy to Google Places (New) Autocomplete API.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'min:2'],
        ]);

        $response = Http::withHeaders([
            'X-Goog-Api-Key' => config('services.google_places.key'),
        ])->post('https://places.googleapis.com/v1/places:autocomplete', [
            'input' => $request->string('query')->value(),
            'includedRegionCodes' => ['cl'],
            'languageCode' => 'es',
        ]);

        $suggestions = $response->json('suggestions', []);

        $predictions = collect($suggestions)
            ->filter(fn (array $s) => isset($s['placePrediction']))
            ->map(fn (array $s) => [
                'place_id' => $s['placePrediction']['placeId'],
                'description' => $s['placePrediction']['text']['text'] ?? '',
                'structured_formatting' => [
                    'main_text' => $s['placePrediction']['structuredFormat']['mainText']['text'] ?? '',
                    'secondary_text' => $s['placePrediction']['structuredFormat']['secondaryText']['text'] ?? '',
                ],
            ])
            ->values()
            ->all();

        return response()->json([
            'predictions' => $predictions,
        ]);
    }

    /**
     * Proxy to Google Places (New) Details API.
     */
    public function details(Request $request): JsonResponse
    {
        $request->validate([
            'place_id' => ['required', 'string'],
        ]);

        $placeId = $request->string('place_id')->value();

        $response = Http::withHeaders([
            'X-Goog-Api-Key' => config('services.google_places.key'),
            'X-Goog-FieldMask' => 'displayName,location,formattedAddress,addressComponents,types',
        ])->get("https://places.googleapis.com/v1/places/{$placeId}", [
            'languageCode' => 'es',
        ]);

        $data = $response->json();

        $addressComponents = collect($data['addressComponents'] ?? []);
        $city = $addressComponents->first(fn (array $c) => in_array('locality', $c['types'] ?? []))['longText'] ?? '';
        $region = $addressComponents->first(fn (array $c) => in_array('administrative_area_level_1', $c['types'] ?? []))['longText'] ?? '';
        $country = $addressComponents->first(fn (array $c) => in_array('country', $c['types'] ?? []))['longText'] ?? 'Chile';

        return response()->json([
            'result' => [
                'name' => $data['displayName']['text'] ?? '',
                'formatted_address' => $data['formattedAddress'] ?? '',
                'geometry' => [
                    'location' => [
                        'lat' => $data['location']['latitude'] ?? null,
                        'lng' => $data['location']['longitude'] ?? null,
                    ],
                ],
                'address_components' => [
                    ['long_name' => $city, 'types' => ['locality']],
                    ['long_name' => $region, 'types' => ['administrative_area_level_1']],
                    ['long_name' => $country, 'types' => ['country']],
                ],
                'types' => $data['types'] ?? [],
            ],
        ]);
    }
}
