<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\BatchUploadController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ComparisonPhotoController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DuplicateCheckController;
use App\Http\Controllers\GooglePlacesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PersonTagController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\PhotoMetadataController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('search', [SearchController::class, 'index'])->name('search');

Route::get('photos', [PhotoController::class, 'index'])->name('photos.index');
Route::get('places', [PlaceController::class, 'index'])->name('places.index');
Route::get('places/{place:slug}', [PlaceController::class, 'show'])->name('places.show');
Route::get('tags', [TagController::class, 'index'])->name('tags.index');
Route::get('tags/{tag:slug}', [TagController::class, 'show'])->name('tags.show');
Route::get('persons', [PersonController::class, 'index'])->name('persons.index');
Route::get('persons/{person}', [PersonController::class, 'show'])->name('persons.show');

Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('users/{user}', [UserProfileController::class, 'show'])->name('users.show');

Route::get('map', [MapController::class, 'index'])->name('map');
Route::get('api/map/photos', [MapController::class, 'photos'])->name('api.map.photos');

Route::get('api/places/search', [PlaceController::class, 'search'])->name('api.places.search');
Route::get('api/tags/search', [TagController::class, 'search'])->name('api.tags.search');

Route::inertia('contribuir', 'Contribute')->name('contribute');

Route::get('contacto', [ContactController::class, 'create'])->name('contact.create');
Route::post('contacto', [ContactController::class, 'store'])->name('contact.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('photos/create', [PhotoController::class, 'create'])->name('photos.create');
    Route::post('photos', [PhotoController::class, 'store'])->name('photos.store');

    Route::get('photos/batch', [BatchUploadController::class, 'create'])->name('photos.batch.create');
    Route::post('photos/batch', [BatchUploadController::class, 'store'])->name('photos.batch.store');
    Route::post('api/photos/check-duplicate', [DuplicateCheckController::class, 'check'])->name('photos.duplicate.check');

    Route::post('photos/{photo}/comments', [CommentController::class, 'store'])->name('photos.comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('photos/{photo}/vote', [VoteController::class, 'store'])->name('photos.vote.store');
    Route::put('photos/{photo}/metadata', [PhotoMetadataController::class, 'update'])->name('photos.metadata.update');
    Route::post('photos/{photo}/report', [ReportController::class, 'store'])->name('photos.report.store');
    Route::post('photos/{photo}/comparisons', [ComparisonPhotoController::class, 'store'])->name('photos.comparisons.store');
    Route::post('photos/{photo}/persons', [PersonTagController::class, 'store'])->name('photos.persons.store');
    Route::delete('photos/{photo}/persons/{person}', [PersonTagController::class, 'destroy'])->name('photos.persons.destroy');

    Route::get('api/google-places/autocomplete', [GooglePlacesController::class, 'autocomplete'])->name('api.google-places.autocomplete');
    Route::get('api/google-places/details', [GooglePlacesController::class, 'details'])->name('api.google-places.details');

    Route::get('api/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::post('api/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.read-all');
    Route::post('api/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');

    Route::get('api/tokens', [ApiTokenController::class, 'index'])->name('api.tokens.index');
    Route::post('api/tokens', [ApiTokenController::class, 'store'])->name('api.tokens.store');
    Route::delete('api/tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api.tokens.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::delete('photos/{photo}', [AdminController::class, 'deletePhoto'])->name('admin.photos.destroy');
    Route::delete('comments/{comment}', [AdminController::class, 'deleteComment'])->name('admin.comments.destroy');
    Route::post('users/{user}/ban', [AdminController::class, 'banUser'])->name('admin.users.ban');
    Route::post('users/{user}/unban', [AdminController::class, 'unbanUser'])->name('admin.users.unban');
});

Route::get('photos/{photo}', [PhotoController::class, 'show'])->name('photos.show');

require __DIR__.'/settings.php';
