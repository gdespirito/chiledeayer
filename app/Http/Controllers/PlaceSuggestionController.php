<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlaceSuggestionRequest;
use App\Models\PlaceSuggestion;
use Illuminate\Http\RedirectResponse;

class PlaceSuggestionController extends Controller
{
    public function store(StorePlaceSuggestionRequest $request): RedirectResponse
    {
        PlaceSuggestion::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'country' => 'Chile',
        ]);

        return back()->with('success', 'Sugerencia de lugar enviada. ¡Gracias!');
    }
}
