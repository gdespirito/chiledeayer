<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeaderboardEntryResource;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    /**
     * Display the leaderboard.
     */
    public function index(Request $request): Response
    {
        $users = User::query()
            ->where('total_points', '>', 0)
            ->orderByDesc('total_points')
            ->withCount('badges')
            ->paginate(50);

        return Inertia::render('Leaderboard', [
            'users' => LeaderboardEntryResource::collection($users),
        ]);
    }
}
