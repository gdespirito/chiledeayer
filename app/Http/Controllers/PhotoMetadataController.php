<?php

namespace App\Http\Controllers;

use App\Events\MetadataEdited;
use App\Http\Requests\UpdatePhotoMetadataRequest;
use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class PhotoMetadataController extends Controller
{
    /**
     * Update photo metadata (wiki-style editing by any authenticated user).
     */
    public function update(UpdatePhotoMetadataRequest $request, Photo $photo): RedirectResponse
    {
        $validated = $request->validated();

        $metadataFields = [
            'description',
            'year_from',
            'year_to',
            'date_precision',
            'place_id',
            'source_credit',
            'heading',
            'pitch',
        ];

        $changes = collect($metadataFields)
            ->filter(fn (string $field) => array_key_exists($field, $validated))
            ->mapWithKeys(fn (string $field) => [$field => $validated[$field]])
            ->toArray();

        if (empty($changes) && ! array_key_exists('tags', $validated)) {
            return back();
        }

        $oldValues = collect($changes)->keys()
            ->mapWithKeys(fn (string $field) => [$field => $photo->getAttribute($field)])
            ->toArray();

        if (! empty($changes)) {
            $photo->update($changes);
        }

        if (array_key_exists('tags', $validated) && is_array($validated['tags'])) {
            $oldValues['tags'] = $photo->tags->pluck('name')->toArray();
            $changes['tags'] = $validated['tags'];

            $tagIds = collect($validated['tags'])->map(function (string $name): int {
                return Tag::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name],
                )->id;
            });

            $photo->tags()->sync($tagIds);
        }

        $photo->revisions()->create([
            'user_id' => $request->user()->id,
            'old_values' => $oldValues,
            'new_values' => $changes,
        ]);

        MetadataEdited::dispatch($photo->id, $request->user()->id, $changes);

        return back()->with('success', 'Metadatos actualizados.');
    }
}
