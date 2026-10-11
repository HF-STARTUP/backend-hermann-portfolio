<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Routes #13 à #15 — login / logout / me (Sanctum, ability « admin »).
 */
class AuthController extends Controller
{
    use ApiResponse;

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        // Réponse identique « email inconnu » / « mot de passe faux » (anti-énumération).
        // validate() et non attempt() : une API sans état ne doit pas ouvrir de
        // session web — sinon le garde sanctum authentifierait ensuite les
        // requêtes par cookie (TransientToken) plutôt que par Bearer.
        if (! Auth::guard('web')->validate($credentials)) {
            return $this->error('Identifiants incorrects.', 401);
        }

        $user = User::query()->where('email', $credentials['email'])->firstOrFail();

        $expirationMinutes = (int) config('sanctum.expiration', 0);
        $expiresAt = now()->addMinutes($expirationMinutes);

        // Token haché en base (Sanctum 4 par défaut), ability unique « admin »
        $token = $user->createToken('admin-dashboard', ['admin'], $expiresAt);

        return $this->success([
            'token' => $token->plainTextToken,
            'expiresAt' => $expiresAt->toISOString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 'Connexion réussie.');
    }

    public function logout(Request $request)
    {
        // Révoque le token courant uniquement (PersonalAccessTokens ;
        // TransientToken — session SPA — n'a rien à révoquer en base)
        $accessToken = $request->user()->currentAccessToken();

        if ($accessToken && method_exists($accessToken, 'delete')) {
            $accessToken->delete();
        }

        return $this->noContent();
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
