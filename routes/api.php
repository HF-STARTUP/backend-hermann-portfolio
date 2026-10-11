<?php

/* Contrôleurs publics */
use App\Http\Controllers\Api\V1\Public\BlogPostController;
use App\Http\Controllers\Api\V1\Public\ContactController;
use App\Http\Controllers\Api\V1\Public\ProjectController;
use App\Http\Controllers\Api\V1\Public\SiteContentController;
use App\Http\Controllers\Api\V1\Public\SocialLinkController;
use App\Http\Controllers\Api\V1\Public\TaxonomyController;
use App\Http\Controllers\Api\V1\Public\TimelineController;

/* Contrôleurs admin — les noms courts se collisionnent avec les publics,
   d'où l'alias « Admin » préfixé. */
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\BlogPostController as AdminBlogPostController;
use App\Http\Controllers\Api\V1\Admin\EducationController;
use App\Http\Controllers\Api\V1\Admin\ExperienceController;
use App\Http\Controllers\Api\V1\Admin\MediaController;
use App\Http\Controllers\Api\V1\Admin\MessageController;
use App\Http\Controllers\Api\V1\Admin\ProjectCategoryController;
use App\Http\Controllers\Api\V1\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Api\V1\Admin\SiteContentController as AdminSiteContentController;
use App\Http\Controllers\Api\V1\Admin\SocialLinkController as AdminSocialLinkController;
use App\Http\Controllers\Api\V1\Admin\TechCategoryController;
use App\Http\Controllers\Api\V1\Admin\TechnologyController;

use Illuminate\Support\Facades\Route;

/* ------------------------------------------------------------------ */
/* PUBLIC — lecture seule + contact                                    */
/* Throttle global "api" : 60/min/IP (AppServiceProvider)              */
/* ------------------------------------------------------------------ */
Route::middleware('throttle:api')->prefix('v1')->group(function () {

    Route::get('site-content',        [SiteContentController::class, 'index']);
    Route::get('projects',            [ProjectController::class, 'index']);       // ?featured=1&category=<id>
    Route::get('projects/{slug}',     [ProjectController::class, 'show']);        // 404 si slug inconnu
    Route::get('blog-posts',          [BlogPostController::class, 'index']);      // ?tag=react
    Route::get('blog-posts/{slug}',   [BlogPostController::class, 'show']);
    Route::get('project-categories',  [TaxonomyController::class, 'projectCategories']);
    Route::get('tech-categories',     [TaxonomyController::class, 'techCategories']);
    Route::get('technologies',        [TaxonomyController::class, 'technologies']);// ?categoryId=
    Route::get('experiences',         [TimelineController::class, 'experiences']);
    Route::get('education',           [TimelineController::class, 'education']);
    Route::get('social-links',        [SocialLinkController::class, 'index']);

    // Anti-spam : 5 messages/min/IP max
    Route::post('contact', [ContactController::class, 'store'])
         ->middleware('throttle:contact');
});

/* ------------------------------------------------------------------ */
/* AUTH SANCTUM                                                        */
/* ------------------------------------------------------------------ */
Route::prefix('v1/auth')->group(function () {
    Route::post('login',  [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::post('logout', [AuthController::class, 'logout'])
          ->middleware(['auth:sanctum', 'ability:admin']);
    Route::get('me',      [AuthController::class, 'me'])
          ->middleware(['auth:sanctum', 'ability:admin']);
});

/* ------------------------------------------------------------------ */
/* ADMIN — auth:sanctum + ability admin                                */
/* Throttle "admin" : 120/min/token                                    */
/* ------------------------------------------------------------------ */
Route::middleware(['auth:sanctum', 'ability:admin', 'throttle:admin'])
    ->prefix('v1/admin')
    ->group(function () {

        /* Projets */
        Route::apiResource('projects', AdminProjectController::class)
             ->parameters(['projects' => 'project']);

        /* Articles de blog */
        Route::apiResource('blog-posts', AdminBlogPostController::class)
             ->parameters(['blog-posts' => 'blogPost']);

        /* Catégories de projets (garde-fou suppression -> 409 si référencée) */
        Route::apiResource('project-categories', ProjectCategoryController::class)
             ->only(['index', 'store', 'update', 'destroy'])
             ->parameters(['project-categories' => 'projectCategory']);

        /* Catégories de technologies (même garde-fou) */
        Route::apiResource('tech-categories', TechCategoryController::class)
             ->only(['index', 'store', 'update', 'destroy'])
             ->parameters(['tech-categories' => 'techCategory']);

        /* Technologies */
        Route::apiResource('technologies', TechnologyController::class)
             ->only(['index', 'store', 'update', 'destroy']);

        /* Médiathèque */
        Route::get('media',                [MediaController::class, 'index']);
        Route::post('media',               [MediaController::class, 'store']);   // multipart/form-data
        Route::delete('media/{mediaItem}', [MediaController::class, 'destroy']);

        /* Messages de contact */
        Route::get('messages',                  [MessageController::class, 'index']);   // ?read=&q=&page=
        Route::patch('messages/{message}/read', [MessageController::class, 'setRead']); // { read: bool }
        Route::delete('messages/{message}',     [MessageController::class, 'destroy']);

        /* Contenu éditable du site — PATCH par section (fusion) */
        Route::patch('site-content/{section}', [AdminSiteContentController::class, 'update']);
              // section dans profile|hero|skills|homeSections|pages|footer

        /* Liens sociaux — remplacement complet (PUT array) */
        Route::put('social-links', [AdminSocialLinkController::class, 'update']);

        /* Parcours — édition unitaire */
        Route::put('experiences/{experience}', [ExperienceController::class, 'update']);
        Route::put('education/{education}',    [EducationController::class, 'update']);
    });
