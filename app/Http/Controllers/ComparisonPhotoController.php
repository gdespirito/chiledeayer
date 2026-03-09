<?php

namespace App\Http\Controllers;

use App\Events\ComparisonUploaded;
use App\Http\Requests\StoreComparisonPhotoRequest;
use App\Jobs\ProcessComparisonUpload;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ComparisonPhotoController extends Controller
{
    /**
     * Store a newly uploaded comparison photo.
     */
    public function store(StoreComparisonPhotoRequest $request, Photo $photo): RedirectResponse
    {
        $validated = $request->validated();
        $file = $request->file('photo');

        $path = $file->storeAs(
            'comparisons/'.$request->user()->id,
            Str::ulid().'.'.$file->getClientOriginalExtension(),
            's3',
        );

        $comparison = $photo->comparisons()->create([
            'user_id' => $request->user()->id,
            'description' => $validated['description'] ?? null,
            'taken_at' => $validated['taken_at'] ?? null,
            'original_path' => $path,
        ]);

        ProcessComparisonUpload::dispatch($comparison, $path);

        ComparisonUploaded::dispatch($comparison->id, $photo->id, $request->user()->id);

        return to_route('photos.show', $photo)
            ->with('success', 'Foto comparativa subida correctamente.');
    }
}
