<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\PhotoMetadataController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::get('photos', [PhotoController::class, 'index'])->name('photos.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('photos/create', [PhotoController::class, 'create'])->name('photos.create');
    Route::post('photos', [PhotoController::class, 'store'])->name('photos.store');

    Route::post('photos/{photo}/comments', [CommentController::class, 'store'])->name('photos.comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('photos/{photo}/vote', [VoteController::class, 'store'])->name('photos.vote.store');
    Route::put('photos/{photo}/metadata', [PhotoMetadataController::class, 'update'])->name('photos.metadata.update');
    Route::post('photos/{photo}/report', [ReportController::class, 'store'])->name('photos.report.store');
});

Route::get('photos/{photo}', [PhotoController::class, 'show'])->name('photos.show');

require __DIR__.'/settings.php';
