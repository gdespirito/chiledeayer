<?php

namespace App\Http\Controllers;

use App\Events\PersonTagged;
use App\Http\Requests\StorePersonTagRequest;
use App\Models\Person;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;

class PersonTagController extends Controller
{
    /**
     * Attach a person to a photo with coordinates.
     */
    public function store(StorePersonTagRequest $request, Photo $photo): RedirectResponse
    {
        $validated = $request->validated();

        if (! empty($validated['person_id'])) {
            $person = Person::findOrFail($validated['person_id']);
        } else {
            $person = Person::create([
                'name' => $validated['person_name'],
                'type' => 'unknown',
            ]);
        }

        $photo->persons()->syncWithoutDetaching([
            $person->id => [
                'x' => $validated['x'] ?? null,
                'y' => $validated['y'] ?? null,
                'label' => $validated['label'] ?? null,
            ],
        ]);

        PersonTagged::dispatch($person->id, $photo->id, $request->user()->id);

        return back()->with('success', 'Persona etiquetada correctamente.');
    }

    /**
     * Detach a person from a photo.
     */
    public function destroy(Photo $photo, Person $person): RedirectResponse
    {
        $photo->persons()->detach($person->id);

        return back()->with('success', 'Etiqueta de persona eliminada.');
    }
}
