<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhotoResource;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    /**
     * Display a listing of tags.
     */
    public function index(): Response
    {
        return Inertia::render('tags/Index', [
            'tags' => TagResource::collection(
                Tag::query()
                    ->withCount('photos')
                    ->orderBy('name')
                    ->paginate(24)
            ),
        ]);
    }

    /**
     * Display the specified tag with its photos.
     */
    public function show(Tag $tag): Response
    {
        return Inertia::render('tags/Show', [
            'tag' => new TagResource($tag->loadCount('photos')),
            'photos' => PhotoResource::collection(
                $tag->photos()
                    ->with(['files', 'user', 'place', 'tags'])
                    ->latest()
                    ->paginate(24)
            ),
        ]);
    }

    /**
     * Search tags by name for autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->string('query')->trim()->value();

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $tags = Tag::query()
            ->where('name', 'like', "%{$query}%")
            ->withCount('photos')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json(TagResource::collection($tags));
    }
}
