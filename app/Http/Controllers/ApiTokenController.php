<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenController extends Controller
{
    /**
     * List the user's API tokens.
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->latest()->get()->map(fn (PersonalAccessToken $token) => [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at,
            'created_at' => $token->created_at,
        ]);

        return response()->json(['tokens' => $tokens]);
    }

    /**
     * Create a new API token.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $token = $request->user()->createToken($validated['name']);

        return response()->json([
            'token' => $token->plainTextToken,
            'message' => 'Token creado. Guarda este token, no se mostrara de nuevo.',
        ], 201);
    }

    /**
     * Revoke an API token.
     */
    public function destroy(Request $request, PersonalAccessToken $token): JsonResponse
    {
        if ($token->tokenable_id !== $request->user()->id) {
            abort(403);
        }

        $token->delete();

        return response()->json(['message' => 'Token revocado.']);
    }
}
