<?php

namespace App\Http\Controllers;

use App\Events\PhotoUploaded;
use App\Http\Requests\BatchUploadRequest;
use App\Jobs\ProcessPhotoUpload;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BatchUploadController extends Controller
{
    /**
     * Show the batch upload form.
     */
    public function create(): Response
    {
        return Inertia::render('photos/BatchCreate');
    }

    /**
     * Process a batch of photo uploads.
     */
    public function store(BatchUploadRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $files = $request->file('photos');
        $overrides = $validated['overrides'] ?? [];
        $count = 0;

        $tagIds = collect();

        if (! empty($validated['shared_tags'])) {
            $tagIds = collect($validated['shared_tags'])->map(function (string $name): int {
                return Tag::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name],
                )->id;
            });
        }

        foreach ($files as $index => $file) {
            $override = $overrides[$index] ?? [];

            $path = $file->storeAs(
                'photos/'.$request->user()->id,
                Str::ulid().'.'.$file->getClientOriginalExtension(),
                's3',
            );

            $photo = $request->user()->photos()->create([
                'title' => $override['title'] ?? $validated['shared_title'] ?? null,
                'year_from' => $override['year_from'] ?? $validated['shared_year_from'],
                'year_to' => $override['year_to'] ?? $validated['shared_year_to'] ?? null,
                'date_precision' => $validated['shared_date_precision'],
                'place_id' => $override['place_id'] ?? $validated['shared_place_id'] ?? null,
                'source_credit' => $validated['shared_source_credit'] ?? null,
            ]);

            if ($tagIds->isNotEmpty()) {
                $photo->tags()->sync($tagIds);
            }

            ProcessPhotoUpload::dispatch($photo, $path);

            PhotoUploaded::dispatch($photo->id, $request->user()->id);

            $count++;
        }

        return to_route('photos.index')
            ->with('success', "{$count} foto(s) subida(s) correctamente. Las miniaturas se generarán en un momento.");
    }
}
