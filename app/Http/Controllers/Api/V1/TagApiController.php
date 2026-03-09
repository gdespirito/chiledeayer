<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TagApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TagResource::collection(
            Tag::query()
                ->withCount('photos')
                ->orderBy('name')
                ->paginate(24)
        );
    }

    public function show(Tag $tag): TagResource
    {
        $tag->loadCount('photos');

        return new TagResource($tag);
    }
}
