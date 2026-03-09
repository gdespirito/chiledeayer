<?php

use App\Http\Controllers\Api\V1\PersonApiController;
use App\Http\Controllers\Api\V1\PhotoApiController;
use App\Http\Controllers\Api\V1\PlaceApiController;
use App\Http\Controllers\Api\V1\TagApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('photos', [PhotoApiController::class, 'index'])->name('api.v1.photos.index');
    Route::get('photos/{photo}', [PhotoApiController::class, 'show'])->name('api.v1.photos.show');
    Route::get('places', [PlaceApiController::class, 'index'])->name('api.v1.places.index');
    Route::get('places/{place:slug}', [PlaceApiController::class, 'show'])->name('api.v1.places.show');
    Route::get('persons', [PersonApiController::class, 'index'])->name('api.v1.persons.index');
    Route::get('persons/{person}', [PersonApiController::class, 'show'])->name('api.v1.persons.show');
    Route::get('tags', [TagApiController::class, 'index'])->name('api.v1.tags.index');
    Route::get('tags/{tag}', [TagApiController::class, 'show'])->name('api.v1.tags.show');

    Route::middleware(['auth:sanctum', 'throttle:uploads'])->group(function () {
        Route::post('photos', [PhotoApiController::class, 'store'])->name('api.v1.photos.store');
        Route::put('photos/{photo}/metadata', [PhotoApiController::class, 'updateMetadata'])->name('api.v1.photos.metadata.update');
    });
});
