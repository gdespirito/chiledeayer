<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GooglePlacesService
{
    /** @var array<int, string> Types relevant for a historical photo archive (landmarks, monuments, geographic, civic) */
    private const LANDMARK_TYPES = [
        'cultural_landmark', 'historical_place', 'monument', 'museum',
        'history_museum', 'art_museum', 'art_gallery', 'castle', 'sculpture', 'fountain',
        'tourist_attraction', 'scenic_spot', 'visitor_center',
        'church', 'synagogue', 'mosque', 'buddhist_temple', 'hindu_temple',
        'park', 'national_park', 'city_park', 'state_park', 'botanical_garden',
        'garden', 'nature_preserve', 'hiking_area', 'woods',
        'beach', 'island', 'lake', 'mountain_peak', 'river',
        'cemetery', 'library', 'university', 'school',
        'city_hall', 'courthouse', 'fire_station',
        'locality', 'sublocality', 'neighborhood',
        'administrative_area_level_1', 'administrative_area_level_2',
    ];

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_places.key');
    }

    /**
     * Search for places using Google Places Autocomplete (New).
     *
     * Results are sorted to prioritize landmarks, monuments, and geographic
     * features over commercial establishments.
     *
     * @return array<int, array{place_id: string, description: string, structured_formatting: array{main_text: string, secondary_text: string}}>
     */
    public function autocomplete(string $query): array
    {
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $this->apiKey,
        ])->post('https://places.googleapis.com/v1/places:autocomplete', [
            'input' => $query,
            'includedRegionCodes' => ['cl'],
            'languageCode' => 'es',
        ]);

        return collect($response->json('suggestions', []))
            ->filter(fn (array $s) => isset($s['placePrediction']))
            ->map(fn (array $s) => [
                'place_id' => $s['placePrediction']['placeId'],
                'description' => $s['placePrediction']['text']['text'] ?? '',
                'structured_formatting' => [
                    'main_text' => $s['placePrediction']['structuredFormat']['mainText']['text'] ?? '',
                    'secondary_text' => $s['placePrediction']['structuredFormat']['secondaryText']['text'] ?? '',
                ],
                'types' => $s['placePrediction']['types'] ?? [],
            ])
            ->sortBy(fn (array $p) => $this->hasLandmarkType($p['types']) ? 0 : 1)
            ->map(fn (array $p) => collect($p)->except('types')->all())
            ->values()
            ->all();
    }

    /**
     * Check if any of the given types match landmark/geographic types.
     *
     * @param  array<int, string>  $types
     */
    private function hasLandmarkType(array $types): bool
    {
        return count(array_intersect($types, self::LANDMARK_TYPES)) > 0;
    }

    /**
     * Get place details from Google Places (New).
     *
     * @return array{name: string, formatted_address: string, geometry: array{location: array{lat: float|null, lng: float|null}}, address_components: array<int, array{long_name: string, types: array<string>}>, types: array<string>}
     */
    public function details(string $placeId): array
    {
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $this->apiKey,
            'X-Goog-FieldMask' => 'displayName,location,formattedAddress,addressComponents,types',
        ])->get("https://places.googleapis.com/v1/places/{$placeId}", [
            'languageCode' => 'es',
        ]);

        $data = $response->json();
        $components = collect($data['addressComponents'] ?? []);

        return [
            'name' => $data['displayName']['text'] ?? '',
            'formatted_address' => $data['formattedAddress'] ?? '',
            'geometry' => [
                'location' => [
                    'lat' => $data['location']['latitude'] ?? null,
                    'lng' => $data['location']['longitude'] ?? null,
                ],
            ],
            'address_components' => [
                ['long_name' => $this->findComponent($components, 'locality'), 'types' => ['locality']],
                ['long_name' => $this->findComponent($components, 'administrative_area_level_1'), 'types' => ['administrative_area_level_1']],
                ['long_name' => $this->findComponent($components, 'country') ?: 'Chile', 'types' => ['country']],
            ],
            'types' => $data['types'] ?? [],
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{types: array<string>, longText: string}>  $components
     */
    private function findComponent(\Illuminate\Support\Collection $components, string $type): string
    {
        return $components->first(fn (array $c) => in_array($type, $c['types'] ?? []))['longText'] ?? '';
    }
}
