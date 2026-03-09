<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    /**
     * Display the interactive map page.
     */
    public function index(): Response
    {
        return Inertia::render('Map');
    }

    /**
     * Return photos within the given bounding box as JSON.
     */
    public function photos(Request $request): JsonResponse
    {
        $request->validate([
            'north' => ['required', 'numeric', 'between:-90,90'],
            'south' => ['required', 'numeric', 'between:-90,90'],
            'east' => ['required', 'numeric', 'between:-180,180'],
            'west' => ['required', 'numeric', 'between:-180,180'],
            'year_from' => ['nullable', 'integer', 'min:1850', 'max:2026'],
            'year_to' => ['nullable', 'integer', 'min:1850', 'max:2026'],
        ]);

        $query = Photo::query()
            ->whereHas('place', function ($q) use ($request) {
                $q->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereBetween('latitude', [(float) $request->south, (float) $request->north])
                    ->whereBetween('longitude', [(float) $request->west, (float) $request->east]);
            })
            ->with(['place:id,latitude,longitude', 'files' => function ($q) {
                $q->where('variant', 'thumb');
            }]);

        if ($request->filled('year_from')) {
            $query->where('year_from', '>=', (int) $request->year_from);
        }

        if ($request->filled('year_to')) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('year_to')
                    ->where('year_from', '<=', (int) $request->year_to)
                    ->orWhere('year_to', '<=', (int) $request->year_to);
            });
        }

        $photos = $query->limit(500)->get();

        return response()->json(
            $photos->map(function (Photo $photo) {
                $thumb = $photo->files->first();

                return [
                    'id' => $photo->id,
                    'lat' => $photo->place->latitude,
                    'lng' => $photo->place->longitude,
                    'description' => $photo->description,
                    'year_from' => $photo->year_from,
                    'thumb_url' => $thumb?->url(),
                ];
            })
        );
    }
}
