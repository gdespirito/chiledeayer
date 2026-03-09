<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhotoResource;
use App\Models\Photo;
use App\Models\Place;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    /**
     * Display search results.
     */
    public function index(Request $request): Response
    {
        $query = $request->input('q', '');
        $placeSlug = $request->input('place');
        $decade = $request->input('decade');
        $tagSlug = $request->input('tag');

        $filters = [];

        if ($placeSlug) {
            $place = Place::query()->where('slug', $placeSlug)->first();
            if ($place) {
                $filters[] = "place_name = '{$place->name}'";
            }
        }

        if ($decade) {
            $decadeStart = (int) $decade;
            $decadeEnd = $decadeStart + 9;
            $filters[] = "year_from >= {$decadeStart}";
            $filters[] = "year_from <= {$decadeEnd}";
        }

        if ($tagSlug) {
            $tag = Tag::query()->where('slug', $tagSlug)->first();
            if ($tag) {
                $filters[] = "tags = '{$tag->name}'";
            }
        }

        $searchQuery = Photo::search($query);

        if (! empty($filters)) {
            $searchQuery->options(['filter' => implode(' AND ', $filters)]);
        }

        $photos = $searchQuery
            ->query(fn ($builder) => $builder->with(['files', 'user', 'place', 'tags']))
            ->paginate(24)
            ->appends($request->query());

        $places = Place::query()
            ->has('photos')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $tags = Tag::query()
            ->has('photos')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Search', [
            'photos' => PhotoResource::collection($photos),
            'filters' => [
                'q' => $query,
                'place' => $placeSlug,
                'decade' => $decade,
                'tag' => $tagSlug,
            ],
            'facets' => [
                'places' => $places,
                'tags' => $tags,
            ],
        ]);
    }
}
