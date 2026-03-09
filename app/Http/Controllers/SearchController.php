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

        $options = [];

        if (! empty($filters)) {
            $options['filter'] = implode(' AND ', $filters);
        }

        $searchQuery = Photo::search($query);

        if (! empty($options)) {
            $searchQuery->options($options);
        }

        $photos = $searchQuery
            ->query(fn ($builder) => $builder->with(['files', 'user', 'place', 'tags']))
            ->paginate(24)
            ->appends($request->query());

        // Get facet distribution from Meilisearch
        $facetQuery = Photo::search($query);
        $facetOptions = array_merge($options, [
            'facets' => ['place_name', 'tags'],
            'limit' => 0,
        ]);
        $facetQuery->options($facetOptions);
        $rawResults = $facetQuery->raw();
        $facetDistribution = $rawResults['facetDistribution'] ?? [];

        $places = Place::query()
            ->has('photos')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Place $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'count' => $facetDistribution['place_name'][$p->name] ?? 0,
            ]);

        $tags = Tag::query()
            ->has('photos')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Tag $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'count' => $facetDistribution['tags'][$t->name] ?? 0,
            ]);

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
