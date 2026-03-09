<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    /**
     * Store a new report for a photo.
     */
    public function store(StoreReportRequest $request, Photo $photo): RedirectResponse
    {
        $photo->reports()->create([
            'user_id' => $request->user()->id,
            'reason' => $request->validated('reason'),
            'duplicate_of_id' => $request->validated('duplicate_of_id'),
        ]);

        return back()->with('success', 'Reporte enviado. Gracias por tu ayuda.');
    }
}
