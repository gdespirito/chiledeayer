<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhotoResource;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserProfileController extends Controller
{
    /**
     * Display the public profile for a user.
     */
    public function show(User $user): Response
    {
        $user->load('badges');
        $user->loadCount(['badges', 'photos']);

        $photos = $user->photos()
            ->with(['files', 'place', 'tags'])
            ->latest()
            ->paginate(24);

        return Inertia::render('users/Show', [
            'user' => new UserProfileResource($user),
            'photos' => PhotoResource::collection($photos),
        ]);
    }
}
