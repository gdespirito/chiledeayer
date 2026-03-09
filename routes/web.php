<?php

use App\Http\Controllers\PhotoController;
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
});

Route::get('photos/{photo}', [PhotoController::class, 'show'])->name('photos.show');

require __DIR__.'/settings.php';
