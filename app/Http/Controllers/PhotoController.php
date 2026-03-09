<?php

namespace App\Http\Controllers;

use App\Events\PhotoUploaded;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PhotoResource;
use App\Http\Resources\RevisionResource;
use App\Jobs\ProcessPhotoUpload;
use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PhotoController extends Controller
{
    /**
     * Display a listing of photos.
     */
    public function index(): Response
    {
        return Inertia::render('photos/Index', [
            'photos' => PhotoResource::collection(
                Photo::query()
                    ->with(['files', 'user', 'place', 'tags'])
                    ->latest()
                    ->paginate(24)
            ),
        ]);
    }

    /**
     * Show the form for creating a new photo.
     */
    public function create(): Response
    {
        return Inertia::render('photos/Create');
    }

    /**
     * Store a newly uploaded photo.
     */
    public function store(StorePhotoRequest $request): RedirectResponse
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

        PhotoUploaded::dispatch($photo->id, $request->user()->id);

        return to_route('photos.show', $photo)
            ->with('success', 'Foto subida correctamente. Las miniaturas se generarán en un momento.');
    }

    /**
     * Display the specified photo.
     */
    public function show(Photo $photo): Response
    {
        $photo->load(['files', 'user', 'place', 'tags', 'votes', 'comments.user', 'revisions.user', 'comparisons.user']);

        return Inertia::render('photos/Show', [
            'photo' => new PhotoResource($photo),
            'comments' => CommentResource::collection($photo->comments->sortByDesc('created_at')),
            'revisions' => RevisionResource::collection($photo->revisions->sortByDesc('created_at')),
        ]);
    }
}
