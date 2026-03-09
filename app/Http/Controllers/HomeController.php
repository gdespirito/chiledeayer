<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhotoResource;
use App\Models\Photo;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the curated home page.
     */
    public function index(): Response
    {
        $eagerLoad = ['files', 'user', 'place', 'tags'];

        $photoOfTheDay = Photo::query()
            ->with($eagerLoad)
            ->whereNotNull('featured_at')
            ->orderByDesc('featured_at')
            ->first();

        if (! $photoOfTheDay) {
            $photoOfTheDay = Photo::query()
                ->with($eagerLoad)
                ->orderByRaw('(upvotes_count - downvotes_count) DESC')
                ->first();
        }

        $latest = Photo::query()
            ->with($eagerLoad)
            ->latest()
            ->limit(8)
            ->get();

        $popular = Photo::query()
            ->with($eagerLoad)
            ->orderByRaw('(upvotes_count - downvotes_count) DESC')
            ->limit(8)
            ->get();

        $needsHelp = Photo::query()
            ->with($eagerLoad)
            ->withCount(['comments', 'tags'])
            ->orderBy('comments_count')
            ->orderBy('tags_count')
            ->limit(8)
            ->get();

        return Inertia::render('Home', [
            'photoOfTheDay' => $photoOfTheDay ? new PhotoResource($photoOfTheDay) : null,
            'latest' => PhotoResource::collection($latest),
            'popular' => PhotoResource::collection($popular),
            'needsHelp' => PhotoResource::collection($needsHelp),
        ]);
    }
}
