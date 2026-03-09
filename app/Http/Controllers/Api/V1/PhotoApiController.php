<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Resources\PhotoResource;
use App\Jobs\ProcessPhotoUpload;
use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class PhotoApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PhotoResource::collection(
            Photo::query()
                ->with(['files', 'user', 'place', 'tags'])
                ->latest()
                ->paginate(24)
        );
    }

    public function show(Photo $photo): PhotoResource
    {
        $photo->load(['files', 'user', 'place', 'tags', 'persons', 'comparisons.user']);

        return new PhotoResource($photo);
    }

    public function store(StorePhotoRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $file = $request->file('photo');

        $path = $file->storeAs(
            'photos/'.$request->user()->id,
            Str::ulid().'.'.$file->getClientOriginalExtension(),
            's3',
        );

        $photo = $request->user()->photos()->create([
            'description' => $validated['description'],
            'year_from' => $validated['year_from'],
            'year_to' => $validated['year_to'] ?? null,
            'date_precision' => $validated['date_precision'],
            'place_id' => $validated['place_id'] ?? null,
            'source_credit' => $validated['source_credit'] ?? null,
            'heading' => $validated['heading'] ?? null,
            'pitch' => $validated['pitch'] ?? null,
        ]);

        if (! empty($validated['tags'])) {
            $tagIds = collect($validated['tags'])->map(function (string $name): int {
                return Tag::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name],
                )->id;
            });
            $photo->tags()->sync($tagIds);
        }

        ProcessPhotoUpload::dispatch($photo, $path);

        return (new PhotoResource($photo->load(['files', 'user', 'place', 'tags'])))
            ->response()
            ->setStatusCode(201);
    }

    public function updateMetadata(Request $request, Photo $photo): PhotoResource
    {
        $validated = $request->validate([
            'description' => ['sometimes', 'string', 'max:2000'],
            'year_from' => ['sometimes', 'integer', 'min:1800', 'max:'.date('Y')],
            'year_to' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y'), 'gte:year_from'],
            'date_precision' => ['sometimes', 'string', 'in:exact,year,decade,circa'],
            'place_id' => ['nullable', 'exists:places,id'],
            'source_credit' => ['nullable', 'string', 'max:500'],
            'heading' => ['nullable', 'numeric', 'min:0', 'max:360'],
            'pitch' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
        ]);

        $metadataFields = ['description', 'year_from', 'year_to', 'date_precision', 'place_id', 'source_credit', 'heading', 'pitch'];

        $changes = collect($metadataFields)
            ->filter(fn (string $field) => array_key_exists($field, $validated))
            ->mapWithKeys(fn (string $field) => [$field => $validated[$field]])
            ->toArray();

        if (! empty($changes)) {
            $photo->update($changes);
        }

        if (array_key_exists('tags', $validated) && is_array($validated['tags'])) {
            $tagIds = collect($validated['tags'])->map(function (string $name): int {
                return Tag::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name],
                )->id;
            });
            $photo->tags()->sync($tagIds);
        }

        return new PhotoResource($photo->load(['files', 'user', 'place', 'tags']));
    }
}
