<?php

namespace App\Http\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * Enveloppe uniforme des réponses JSON :
 * { "success": bool, "message": string|null, "data": ... }
 */
trait ApiResponse
{
    /**
     * Réponse de succès.
     *
     * @param  mixed  $data  Charge utile : Resource, tableau ou null
     */
    protected function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Réponse d'erreur.
     *
     * @param  array<string, array<int, string>>|null  $errors  Erreurs par champ (422)
     * @param  mixed  $data  Charge utile optionnelle (ex. { usages } sur un 409)
     */
    protected function error(string $message, int $status = 400, ?array $errors = null, mixed $data = null): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }

    /**
     * Réponse 204 sans contenu (destroy).
     */
    protected function noContent(): \Illuminate\Http\Response
    {
        return response()->noContent();
    }
}
