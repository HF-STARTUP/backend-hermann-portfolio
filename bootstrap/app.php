<?php

/*
|--------------------------------------------------------------------------
| Configuration de l'application (Laravel 12+)
|--------------------------------------------------------------------------
| Middlewares et rendu des exceptions se déclarent ici — il n'y a plus
| de app/Http/Kernel.php. Voir la spécification §2.4 et §4.4.
*/

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API : forcer le JSON + en-têtes de sécurité (spec §7)
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // En-têtes de sécurité (X-Frame-Options, nosniff, Referrer-Policy…)
        $middleware->append(\App\Http\Middleware\SecureHeaders::class);

        // Alias Sanctum
        $middleware->alias([
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Toute erreur sur api/* est rendue en JSON
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        // 422 — Validation échouée
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $r) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de la validation.',
                'errors' => $e->errors(),
            ], 422);
        });

        // 401 — Non authentifié
        $exceptions->render(function (AuthenticationException $e, Request $r) {
            return response()->json(['success' => false, 'message' => 'Non authentifié.'], 401);
        });

        // 403 — Interdit (token valide mais ability « admin » absente)
        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, Request $r) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        });

        // 404 — Introuvable
        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $r) {
            return response()->json(['success' => false, 'message' => 'Ressource introuvable.'], 404);
        });

        // 400 — Requête invalide / JSON malformé
        $exceptions->render(function (\Symfony\Component\HttpFoundation\Exception\BadRequestException $e, Request $r) {
            return response()->json(['success' => false, 'message' => 'Requête invalide.'], 400);
        });

        // 413 / 415 — Uploads
        $exceptions->render(function (\App\Exceptions\UploadedFileTooLarge $e, Request $r) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 413);
        });
        $exceptions->render(function (\App\Exceptions\UnsupportedMediaType $e, Request $r) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 415);
        });

        // 429 — Trop de requêtes
        $exceptions->render(function (ThrottleRequestsException $e, Request $r) {
            return response()->json(['success' => false, 'message' => 'Trop de tentatives. Réessayez plus tard.'], 429);
        });
    })->create();
