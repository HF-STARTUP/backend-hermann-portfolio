# Spécification backend — Portfolio Hermann FOMO (Laravel 12)

> **Document destiné à un agent IA développeur backend.** Il décrit intégralement le backend
> REST à construire pour alimenter le portfolio frontend Next.js existant
> (repo `frontend-hermann-portfolio`). Suis-le à la lettre : structure du code, format des
> réponses, routes API et exigences de sécurité sont un **contrat** — le frontend consommera
> ces endpoints sans autre documentation.
>
> Sources de vérité frontend (à lire pour toute ambiguïté sur un champ ou une donnée) :
> - `src/types/portfolio.type.ts` — tous les modèles TypeScript (contrat des réponses JSON) ;
> - `src/stores/usePortfolioStore.ts` — chaque action du store = un endpoint (voir annexe B) ;
> - `src/schema/portfolio.schema.ts`, `src/schema/contact.schema.ts` — règles de validation à transposer ;
> - `src/mocks/portfolio.mock.ts` — **données de seed exactes** (contenus, slugs, textes).

---

## Sommaire

1. [Contexte, stack & conventions](#1-contexte-stack--conventions)
2. [Architecture du code](#2-architecture-du-code)
3. [Schéma de base de données](#3-schéma-de-base-de-données)
4. [Format des réponses (contrat exact)](#4-format-des-réponses-contrat-exact)
5. [Routes API — tableau exhaustif](#5-routes-api--tableau-exhaustif)
6. [Validation serveur (FormRequests)](#6-validation-serveur-formrequests)
7. [Sécurité — checklist impérative](#7-sécurité--checklist-impérative)
8. [Seeders](#8-seeders)
9. [Tests & vérification finale](#9-tests--vérification-finale)
10. [Annexes](#10-annexes)

---

## 1. Contexte, stack & conventions

### 1.1 Contexte

Le portfolio public (accueil, à propos, projets, blog, contact) et l'espace d'administration
(`/admin/*`) existent côté frontend. Aujourd'hui les données vivent en mémoire (zustand,
initialisées par les mocks) ; chaque point de branchement porte un commentaire
`// TODO backend.hermannfomo.dev : ...`. Ce backend doit :

- servir les données publiques du portfolio (JSON) ;
- exposer les opérations d'administration (CRUD complet, contenu du site, médias, messages) ;
- recevoir les messages du formulaire de contact ;
- héberger les médias uploadés depuis l'admin (sélecteur d'images avec aperçu).

### 1.2 Stack imposée

| Élément | Choix |
|---|---|
| Framework | **Laravel 12** (PHP >= 8.2, extensions `bcmath`, `pdo_mysql`, `intl`, `gd` ou `imagick`) |
| Base de données | **MySQL 8** ou **MariaDB 10.11+** |
| Auth | **Laravel Sanctum — personal access tokens (Bearer)** |
| Format | REST JSON uniquement, préfixe **`/api/v1`** |
| Stockage médias | Disque `public` Laravel (`storage/app/public` + lien `public/storage`) |

### 1.3 Création du projet

```bash
composer create-project laravel/laravel backend-hermannfomo
cd backend-hermannfomo
php artisan install:api        # installe Sanctum + crée routes/api.php + migration personal_access_tokens
php artisan storage:link       # expose les médias uploadés
```

### 1.4 Configuration `.env` (extraits)

```env
APP_NAME="Hermann FOMO API"
APP_ENV=production            # local pour le développement
APP_KEY=                      # php artisan key:generate
APP_DEBUG=false               # JAMAIS true en production
APP_URL=https://backend.hermannfomo.dev

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hermannfomo
DB_USERNAME=...
DB_PASSWORD=...

FRONTEND_URL=https://hermannfomo.dev   # utilisée par config/cors.php
SANCTUM_TOKEN_EXPIRATION=10080         # expiration des tokens en minutes (7 jours)

# Compte admin créé par le seeder (jamais commité en dur ailleurs)
ADMIN_EMAIL=admin@hermannfomo.dev
ADMIN_PASSWORD=            # à définir au déploiement, haché par le seeder
```

### 1.5 Spécificité Laravel 12 — à ne pas rater

Laravel 12 **n'a plus de `app/Http/Kernel.php`**. Les middlewares et le rendu des exceptions
se déclarent dans **`bootstrap/app.php`** avec `->withMiddleware()` et `->withExceptions()`
(exemples complets aux sections 2.4 et 4.4). Les rate limiters se déclarent dans
`AppServiceProvider::boot()` via `RateLimiter::for(...)`.

### 1.6 Conventions obligatoires

1. **Toutes les réponses JSON exposent les champs en camelCase**, identiques aux interfaces
   TypeScript (`imageUrl`, `techStack`, `coverYoutubeUrl`, `sentAt`…). La base de données
   reste en snake_case : le mapping se fait dans les **API Resources** (section 4.3).
2. **Dates en ISO-8601 UTC** (`2026-08-22T10:30:00.000000Z`). Les dates « jour »
   (`publishedAt`, `startDate`, `endDate`) sont renvoyées au format `Y-m-d`.
3. **Enveloppe uniforme** : `{ "success": bool, "message": string|null, "data": ... }`
   (détail et exemples en section 4).
4. **IDs en chaînes ULID** (26 caractères) pour coller aux `id: string` du frontend.
5. Aucune logique métier dans les contrôleurs : ils valident (FormRequest), délèguent aux
   Services si nécessaire et répondent via une Resource.
6. Tout le code, les messages d'erreur et les commentaires sont **en français** (les messages
   de validation sont donnés mot pour mot en section 6).

---

## 2. Architecture du code

### 2.1 Arborescence imposée

```
app/
├── Enums/
│   └── CoverType.php                     # enum string: image, video, youtube
├── Http/
│   ├── Middleware/
│   │   └── ForceJsonResponse.php         # force Accept: application/json
│   ├── Controllers/Api/V1/
│   │   ├── Public/
│   │   │   ├── SiteContentController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── BlogPostController.php
│   │   │   ├── TaxonomyController.php    # catégories (projets + technos) & technologies
│   │   │   ├── TimelineController.php    # expériences + formations
│   │   │   ├── SocialLinkController.php
│   │   │   └── ContactController.php
│   │   └── Admin/
│   │       ├── AuthController.php
│   │       ├── ProjectController.php
│   │       ├── BlogPostController.php
│   │       ├── ProjectCategoryController.php
│   │       ├── TechCategoryController.php
│   │       ├── TechnologyController.php
│   │       ├── MediaController.php
│   │       ├── MessageController.php
│   │       ├── SiteContentController.php
│   │       ├── SocialLinkController.php
│   │       ├── ExperienceController.php
│   │       └── EducationController.php
│   ├── Requests/
│   │   ├── Auth/LoginRequest.php
│   │   ├── Contact/StoreContactMessageRequest.php
│   │   ├── Projects/{StoreProjectRequest,UpdateProjectRequest}.php
│   │   ├── BlogPosts/{StoreBlogPostRequest,UpdateBlogPostRequest}.php
│   │   ├── Categories/{StoreProjectCategoryRequest,UpdateProjectCategoryRequest,StoreTechCategoryRequest,UpdateTechCategoryRequest}.php
│   │   ├── Technologies/{StoreTechnologyRequest,UpdateTechnologyRequest}.php
│   │   ├── Media/StoreMediaRequest.php
│   │   ├── Messages/{UpdateMessageReadRequest}.php
│   │   ├── SiteContent/UpdateSiteContentRequest.php
│   │   ├── SocialLinks/UpdateSocialLinksRequest.php
│   │   └── Timeline/{UpdateExperienceRequest,UpdateEducationRequest}.php
│   └── Resources/
│       ├── ProjectCategoryResource.php
│       ├── ProjectResource.php
│       ├── TechCategoryResource.php
│       ├── TechnologyResource.php
│       ├── BlogPostResource.php
│       ├── ContactMessageResource.php
│       ├── MediaItemResource.php
│       ├── ExperienceResource.php
│       ├── EducationResource.php
│       ├── SocialLinkResource.php
│       └── SiteContentResource.php
├── Models/
│   ├── User.php                          # HasApiTokens (Sanctum)
│   ├── ProjectCategory.php
│   ├── Project.php
│   ├── TechCategory.php
│   ├── Technology.php
│   ├── BlogPost.php
│   ├── ContactMessage.php
│   ├── MediaItem.php
│   ├── Experience.php
│   ├── Education.php
│   └── SocialLink.php
├── Rules/
│   ├── YoutubeUrl.php                    # extrait l'ID YouTube (watch/youtu.be/shorts/embed)
│   └── HostedImageOrMediaPath.php        # URL http(s) ou chemin /storage/... retourné par POST /media
└── Services/
    ├── MediaService.php                  # upload, suppression, URL absolue
    └── SiteContentService.php            # lecture arbre complet + fusion des PATCH par section
```

### 2.2 Conventions des modèles

Chaque modèle Eloquent :

- utilise le trait `HasUlids` (sauf `User` qui garde sa migration standard adaptée en ULID) ;
- déclare un **`$fillable` explicite** (jamais de `guarded = []` — anti mass-assignment) ;
- caste ses colonnes JSON : `protected function casts(): array` (Laravel 12) —
  ex. `'techStack' => 'array'` n'existe pas : les colonnes sont snake_case, donc
  `'tech_stack' => 'array'`, `'tags' => 'array'`, `'content' => 'array'`, `'links'` n'existe
  pas (colonnes séparées) ;
- expose les **appends/accessors utiles** uniquement quand la Resource en a besoin.

Relations :

- `ProjectCategory hasMany Project` / `Project belongsTo ProjectCategory` ;
- `TechCategory hasMany Technology` / `Technology belongsTo TechCategory` ;
- `Project` et `BlogPost` référencent éventuellement `MediaItem` par URL (pas de FK) —
  les URLs de médias sont stockées comme chemins relatifs (`media/AbCdEf....jpg`) et
  transformées en URLs absolues par les Resources via `MediaService::absoluteUrl()`.

### 2.3 Services

**`MediaService`** — unique point de contact avec le disque :

```php
public function store(UploadedFile $file): MediaItem;   // nommage hashé, disque public
public function delete(MediaItem $media): void;         // supprime le fichier + la ligne
public function absoluteUrl(?string $path): ?string;    // null-safe, Storage::url + APP_URL
```

**`SiteContentService`** — gère la table `site_content` (une ligne par section, `data` JSON) :

```php
public function tree(): array;                          // arbre SiteContent complet (public)
public function patchSection(string $section, array $patch): array; // fusion profonde puis retour de la section
```

La fusion du PATCH reproduit exactement `updateSiteContent(section, patch)` du store
zustand : `{...sectionActuelle, ...patch}` (fusion de premier niveau ; `skills` et
`homeSections.*` sont remplacés en bloc quand présents dans le patch).

### 2.4 `bootstrap/app.php` (référence)

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API : forcer le JSON + headers de sécurité (section 7)
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
        // Alias Sanctum
        $middleware->alias([
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Rendu uniforme des erreurs : voir section 4.4
    })->create();
```

---

## 3. Schéma de base de données

Toutes les migrations sont créées avec `php artisan make:migration`. Les clés primaires sont
des **ULID** (`$table->ulid('id')->primary();`). L'ordre de création respecte les FK.

### 3.1 `users` (adaptation de la migration standard)

```php
Schema::create('users', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');                 // haché bcrypt/argon2
    $table->timestamps();
});
// + la migration personal_access_tokens fournie par Sanctum (inchangée)
```

Un seul compte : l'administrateur du portfolio. Aucune inscription publique — **pas de
route register**.

### 3.2 Catégories & projets

```php
// project_categories — interface TS ProjectCategory { id, label }
Schema::create('project_categories', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('label', 100)->unique();     // ex: "Web", "Mobile", "Fullstack", "Design"
    $table->timestamps();
});

// projects — interface TS Project
Schema::create('projects', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('project_category_id')->constrained(table: 'project_categories')->restrictOnDelete();
    $table->string('slug', 255)->unique();      // regex ^[a-z0-9]+(?:-[a-z0-9]+)*$
    $table->string('title', 150);
    $table->string('role', 120);                // ex: "Développeur Frontend"
    $table->text('description');                // courte (carte)
    $table->text('long_description')->nullable();// page détail (fallback description)
    $table->string('image_url', 500)->nullable();   // chemin média (/storage/media/x.jpg) ou URL externe
    $table->json('tech_stack');                 // string[] — noms de technologies
    $table->string('demo_url', 500)->nullable();
    $table->string('github_url', 500)->nullable();
    $table->boolean('featured')->default(false);
    $table->date('start_date');                 // Y-m-d
    $table->date('end_date')->nullable();
    $table->timestamps();
});
```

> Le frontend expose `links: { demoUrl?, githubUrl? }` : les deux colonnes séparées
> `demo_url`/`github_url` sont **aplaties dans l'objet links par `ProjectResource`**.

### 3.3 Technologies

```php
// tech_categories — interface TS TechCategory { id, name }
Schema::create('tech_categories', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('name', 100)->unique();      // ex: "Frontend", "Backend", "Mobile"...
    $table->timestamps();
});

// technologies — interface TS Technology { id, name, categoryId, imageUrl? }
Schema::create('technologies', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('tech_category_id')->constrained(table: 'tech_categories')->restrictOnDelete();
    $table->string('name', 100)->unique();      // unicité globale : les noms alimentent le TagInput
    $table->string('image_url', 500)->nullable();   // image personnalisée uploadée via /admin
    $table->timestamps();
});
```

> Les icônes de marque des seeds (`icon` react-icons côté frontend) ne sont **pas stockées**
> en base : le frontend garde sa table seed `icon ↔ nom`. La colonne `image_url` sert aux
> images personnalisées choisies dans `/admin/technologies`.

### 3.4 Blog

```php
// blog_posts — interface TS BlogPost
Schema::create('blog_posts', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('slug', 255)->unique();
    $table->string('title', 200);
    $table->string('excerpt', 500);
    $table->string('cover_image_url', 500)->nullable();  // aussi poster pour vidéo/YouTube
    $table->enum('cover_type', ['image', 'video', 'youtube'])->default('image');
    $table->string('cover_video_url', 500)->nullable();  // requis si cover_type = video
    $table->string('cover_youtube_url', 500)->nullable();// requis si cover_type = youtube (URL brute validée)
    $table->json('tags');                        // string[] min 1
    $table->date('published_at');                // renvoyé "publishedAt": "2026-08-22"
    $table->unsignedTinyInteger('read_time_minutes');     // 1..120
    $table->json('content');                     // BlogContentBlock[] — voir 4.3
    $table->timestamps();
});
```

Structure d'un bloc de `content` (type union TS `BlogContentBlock`, validé en section 6.5) :

```json
[
  { "type": "paragraph", "text": "..." },
  { "type": "heading",   "text": "..." },
  { "type": "quote",     "text": "...", "author": "Optionnel" },
  { "type": "code",      "text": "...", "language": "ts" }
]
```

### 3.5 Contact, médias, parcours, liens sociaux

```php
// contact_messages — interface TS ContactMessage
Schema::create('contact_messages', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('name', 120);
    $table->string('email', 180);
    $table->string('subject', 200);
    $table->text('message');                    // 10..2000 caractères
    $table->timestamp('read_at')->nullable();   // read = read_at !== null
    $table->timestamps();                       // created_at = sentAt
});

// media_items — interface TS MediaItem { id, name, src }
Schema::create('media_items', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('name', 255);                // nom original lisible
    $table->string('disk', 30)->default('public');
    $table->string('path', 500);                // chemin relatif : media/<ulid>.<ext>
    $table->string('mime_type', 100);
    $table->unsignedInteger('size');            // octets
    $table->timestamps();
});

// experiences — interface TS Experience
Schema::create('experiences', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('company', 150);
    $table->string('position', 150);
    $table->text('description');
    $table->string('location', 150)->nullable();
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->timestamps();
});

// education — interface TS Education
Schema::create('education', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('school', 150);
    $table->string('degree', 150);
    $table->text('description')->nullable();
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->timestamps();
});

// social_links — interface TS SocialLink { id, label, url, iconName }
Schema::create('social_links', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('label', 100);
    $table->string('url', 500);
    $table->string('icon_name', 50);            // nom lucide résolu par le frontend (preset)
    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->timestamps();
});
```

### 3.6 Contenu éditable du site (`site_content`)

Une ligne **par section** de l'interface TS `SiteContent`. C'est ce qui permet au PATCH
admin de cibler une section exactement comme `updateSiteContent(section, patch)` :

```php
Schema::create('site_content', function (Blueprint $table) {
    $table->string('section', 30)->primary();   // profile|hero|skills|homeSections|pages|footer
    $table->json('data');                       // contenu complet de la section
    $table->timestamps();
});
```

| `section` | Contenu de `data` (interface TS) |
|---|---|
| `profile` | `ProfileContent` |
| `hero` | `HeroContent` (avec `stats: StatItem[]`) |
| `skills` | tableau `SkillCard[]` |
| `homeSections` | `HomeSectionsContent` (about, technologies, projects, contact) |
| `pages` | `PagesContent` (about, projects, blog) |
| `footer` | `FooterContent` |

> Les expériences, formations et liens sociaux restent dans leurs propres tables (relations
> plus naturelles), mais leur lecture publique est agrégée dans l'arbre si besoin — le
> contrat public retenu est détaillé en section 5.1.

---

## 4. Format des réponses (contrat exact)

### 4.1 Enveloppe uniforme

Toute réponse (y compris les erreurs) suit cette enveloppe :

```jsonc
{
  "success": true,          // false pour toutes les erreurs
  "message": "...",         // court, en français (null toléré sur les GET)
  "data": { ... }           // charge utile : Resource, tableau, ou null
}
```

Implémentation : un trait `Http/Concerns/ApiResponse.php` avec `success($data, ?string $message, int $status = 200)`
et `error(string $message, int $status, array $errors = [])`, utilisé par tous les contrôleurs.
Les erreurs sont par ailleurs normalisées globalement via `->withExceptions()` (section 4.4).

### 4.2 Codes HTTP utilisés

| Code | Signification | Cas concrets |
|---|---|---|
| 200 | OK | lecture, mise à jour |
| 201 | Créé | store (POST) — renvoie la ressource créée |
| 204 | Supprimé sans contenu | destroy — **corps vide** |
| 400 | Requête invalide | JSON malformé |
| 401 | Non authentifié | token absent/expiré/révoqué |
| 403 | Interdit | token valide mais ability `admin` absente |
| 404 | Introuvable | slug/id inconnu, route inexistante |
| 409 | Conflit | suppression d'une catégorie encore référencée |
| 422 | Validation échouée | erreurs par champ dans `errors` |
| 429 | Trop de requêtes | rate limit dépassé (login, contact…) |

### 4.3 API Resources — mapping snake_case → camelCase

Chaque Resource reproduit **exactement** une interface TS. Extraits de référence :

```php
// ProjectResource — interface TS Project
public function toArray(Request $request): array {
    return [
        'id'              => $this->id,
        'slug'            => $this->slug,
        'title'           => $this->title,
        'role'            => $this->role,
        'description'     => $this->description,
        'longDescription' => $this->long_description,
        'imageUrl'        => $this->image_url ? MediaService::absoluteUrl($this->image_url) : '',
        'techStack'       => $this->tech_stack,             // cast array
        'category'        => $this->project_category_id,    // id string (contrat TS Project.category)
        'links'           => [
            'demoUrl'   => $this->demo_url,
            'githubUrl' => $this->github_url,
        ],
        'featured'        => $this->featured,
        'startDate'       => $this->start_date?->format('Y-m-d'),
        'endDate'         => $this->end_date?->format('Y-m-d'),
    ];
}
```

```php
// BlogPostResource — interface TS BlogPost
return [
    'id'               => $this->id,
    'slug'             => $this->slug,
    'title'            => $this->title,
    'excerpt'          => $this->excerpt,
    'coverImageUrl'    => $this->cover_image_url ? MediaService::absoluteUrl($this->cover_image_url) : '',
    'coverType'        => $this->cover_type->value,       // enum CoverType
    'coverVideoUrl'    => $this->when($this->cover_video_url, $this->cover_video_url),
    'coverYoutubeUrl'  => $this->when($this->cover_youtube_url, $this->cover_youtube_url),
    'tags'             => $this->tags,
    'publishedAt'      => $this->published_at->format('Y-m-d'),
    'readTimeMinutes'  => $this->read_time_minutes,
    'content'          => $this->content,                 // blocs typés tels quels
];
```

```php
// TechnologyResource / TechCategoryResource / ProjectCategoryResource
return [ 'id' => $this->id, 'name' => $this->name,
         'categoryId' => $this->tech_category_id,                       // TechnologyResource
         'imageUrl' => MediaService::absoluteUrl($this->image_url) ];   // TechnologyResource
return [ 'id' => $this->id, 'name' => $this->name ];                   // TechCategoryResource
return [ 'id' => $this->id, 'label' => $this->label ];                 // ProjectCategoryResource
```

```php
// ContactMessageResource — interface TS ContactMessage
return [
    'id'      => $this->id,
    'name'    => $this->name,
    'email'   => $this->email,
    'subject' => $this->subject,
    'message' => $this->message,
    'sentAt'  => $this->created_at->toISOString(),   // ISO-8601 UTC
    'read'    => $this->read_at !== null,
];

// MediaItemResource — interface TS MediaItem
return [ 'id' => $this->id, 'name' => $this->name,
         'src' => MediaService::absoluteUrl($this->path) ];

// ExperienceResource / EducationResource — camelCase direct
// (company, position, description, location?, startDate Y-m-d, endDate?, sortOrder)

// SocialLinkResource
return [ 'id' => $this->id, 'label' => $this->label,
         'url' => $this->url, 'iconName' => $this->icon_name ];
```

**`SiteContentResource`** renvoie l'arbre complet du contenu éditable, structure identique à
l'interface TS `SiteContent` :

```jsonc
{
  "profile":     { "fullName": "...", "firstName": "...", "lastName": "...", "logoInitials": "HF",
                   "roleLine": "...", "techLine": "...", "location": "Résident à Douala",
                   "email": "...", "cvUrl": "" },
  "hero":        { "availabilityBadge": "...", "description": "...", "ctaLabel": "...",
                   "scrollHint": "...", "stats": [{ "id": "...", "value": "+2", "label": "ans d'expérience" }] },
  "skills":      [{ "id": "...", "name": "...", "iconName": "Code2", "features": ["..."] }],
  "homeSections":{ "about": {...}, "technologies": {...}, "projects": {...}, "contact": {...} },
  "pages":       { "about": {...}, "projects": {...}, "blog": {...} },
  "footer":      { "baseline": "...", "copyrightPrefix": "...", "copyrightSuffix": "..." }
}
```
(Champs exacts de chaque sous-objet : `src/types/portfolio.type.ts`.)

### 4.4 Rendu global des exceptions (`bootstrap/app.php ->withExceptions`)

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (ValidationException $e, Request $r) {
        return response()->json([
            'success' => false,
            'message' => 'Échec de la validation.',
            'errors'  => $e->errors(),
        ], 422);
    });
    $exceptions->render(function (AuthenticationException $e, Request $r) {
        return response()->json(['success' => false, 'message' => 'Non authentifié.'], 401);
    });
    $exceptions->render(function (AccessDeniedHttpException $e, Request $r) {
        return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
    });
    $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $r) {
        return response()->json(['success' => false, 'message' => 'Ressource introuvable.'], 404);
    });
    $exceptions->render(function (ThrottleRequestsException $e, Request $r) {
        return response()->json(['success' => false, 'message' => 'Trop de tentatives. Réessayez plus tard.'], 429);
    });
})
```

Toute autre exception tombe sur le handler 500 de Laravel : avec `APP_DEBUG=false`, masquer
les détails et renvoyer `{"success": false, "message": "Erreur interne du serveur."}`
(via `$exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*'))` + handler générique).

### 4.5 Pagination (uniquement messages admin)

Les volumes publics sont petits → **pas de pagination côté public**. Seul
`GET /api/v1/admin/messages` est paginé (15/page) et renvoie :

```jsonc
{
  "success": true,
  "message": null,
  "data": {
    "items":   [ /* ContactMessageResource[] */ ],
    "pagination": { "currentPage": 1, "lastPage": 2, "perPage": 15, "total": 18 }
  }
}
```

---

## 5. Routes API — tableau exhaustif

### 5.1 `routes/api.php` complet

```php
<?php

/* Contrôleurs publics */
use App\Http\Controllers\Api\V1\Public\BlogPostController;
use App\Http\Controllers\Api\V1\Public\ContactController;
use App\Http\Controllers\Api\V1\Public\ProjectController;
use App\Http\Controllers\Api\V1\Public\SiteContentController;
use App\Http\Controllers\Api\V1\Public\SocialLinkController;
use App\Http\Controllers\Api\V1\Public\TaxonomyController;
use App\Http\Controllers\Api\V1\Public\TimelineController;

/* Contrôleurs admin — les noms courts se collisionnent avec les publics
   (ProjectController, BlogPostController, SiteContentController,
   SocialLinkController), d'où l'alias « Admin » préfixé. */
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
use App\Http\Controllers\Api\V1\Admin\AuthController;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/* ------------------------------------------------------------------ */
/* PUBLIC — lecture seule + contact                                    */
/* Throttle global "api" : 60/min/IP (déclaré dans AppServiceProvider) */
/* ------------------------------------------------------------------ */
Route::middleware('throttle:api')->prefix('v1')->group(function () {

    Route::get('site-content',        [SiteContentController::class, 'index']);
    Route::get('projects',            [ProjectController::class, 'index']);       // ?featured=1&category=<id>
    Route::get('projects/{slug}',     [ProjectController::class, 'show']);        // {slug}, 404 si absent
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
             ->parameters(['projects' => 'project']);            // liaison par ULID

        /* Articles de blog */
        Route::apiResource('blog-posts', AdminBlogPostController::class)
             ->parameters(['blog-posts' => 'blogPost']);

        /* Catégories de projets (garde-fou suppression → 409 si référencée) */
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
        Route::get('media',        [MediaController::class, 'index']);
        Route::post('media',       [MediaController::class, 'store']);   // multipart/form-data
        Route::delete('media/{mediaItem}', [MediaController::class, 'destroy']);

        /* Messages de contact */
        Route::get('messages',               [MessageController::class, 'index']);    // ?read=&q=&page=
        Route::patch('messages/{message}/read', [MessageController::class, 'setRead']); // { read: bool }
        Route::delete('messages/{message}',  [MessageController::class, 'destroy']);

        /* Contenu éditable du site — PATCH par section (fusion) */
        Route::patch('site-content/{section}', [AdminSiteContentController::class, 'update']);
              // section ∈ profile|hero|skills|homeSections|pages|footer

        /* Liens sociaux — remplacement complet (PUT array) */
        Route::put('social-links', [AdminSocialLinkController::class, 'update']);

        /* Parcours — édition unitaire */
        Route::put('experiences/{experience}', [ExperienceController::class, 'update']);
        Route::put('education/{education}',    [EducationController::class, 'update']);
    });
```

Rate limiters à déclarer dans `AppServiceProvider::boot()` :

```php
RateLimiter::for('api', fn (Request $r) => Limit::perMinute(60)->by($r->ip()));
RateLimiter::for('contact', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
RateLimiter::for('auth', fn (Request $r) => Limit::perMinute(5)->by($r->ip().'|'.$r->input('email')));
RateLimiter::for('admin', fn (Request $r) => Limit::perMinute(120)->by(optional($r->user())->id ?: $r->ip()));
```

### 5.2 Tableau détaillé des routes

Colonnes : méthode · URI · contrôleur · FormRequest · Resource · codes retour.

**Public**

| # | Méthode | URI | Contrôleur@action | Validation | Resource | Codes |
|---|---|---|---|---|---|---|
| 1 | GET | `/api/v1/site-content` | `Public\SiteContentController@index` | — | `SiteContentResource` | 200 |
| 2 | GET | `/api/v1/projects` | `Public\ProjectController@index` | query `featured` bool·`category` exists | `[ProjectResource]` triés `start_date` desc | 200 |
| 3 | GET | `/api/v1/projects/{slug}` | `Public\ProjectController@show` | slug string | `ProjectResource` | 200 · **404** |
| 4 | GET | `/api/v1/blog-posts` | `Public\BlogPostController@index` | query `tag` string | `[BlogPostResource]` triés `published_at` desc | 200 |
| 5 | GET | `/api/v1/blog-posts/{slug}` | `Public\BlogPostController@show` | slug string | `BlogPostResource` | 200 · **404** |
| 6 | GET | `/api/v1/project-categories` | `Public\TaxonomyController@projectCategories` | — | `[ProjectCategoryResource]` | 200 |
| 7 | GET | `/api/v1/tech-categories` | `Public\TaxonomyController@techCategories` | — | `[TechCategoryResource]` | 200 |
| 8 | GET | `/api/v1/technologies` | `Public\TaxonomyController@technologies` | query `categoryId` exists | `[TechnologyResource]` | 200 |
| 9 | GET | `/api/v1/experiences` | `Public\TimelineController@experiences` | — | `[ExperienceResource]` triés `sort_order` asc | 200 |
| 10 | GET | `/api/v1/education` | `Public\TimelineController@education` | — | `[EducationResource]` triés `sort_order` asc | 200 |
| 11 | GET | `/api/v1/social-links` | `Public\SocialLinkController@index` | — | `[SocialLinkResource]` triés `sort_order` asc | 200 |
| 12 | POST | `/api/v1/contact` | `Public\ContactController@store` | §6.1 | `null` (`data: null`) | **201** · 422 · 429 |

> **#12** crée un `ContactMessage` avec `read_at = NULL`. Réponse :
> `{"success": true, "message": "Votre message a bien été envoyé. Je vous répondrai dans les plus brefs délais.", "data": null}`.

**Authentification**

| # | Méthode | URI | Contrôleur@action | Validation | Codes |
|---|---|---|---|---|---|
| 13 | POST | `/api/v1/auth/login` | `Admin\AuthController@login` | §6.2 | 200 · 401 · 422 · 429 |
| 14 | POST | `/api/v1/auth/logout` | `Admin\AuthController@logout` | token requis | 204 · 401 |
| 15 | GET | `/api/v1/auth/me` | `Admin\AuthController@me` | token requis | 200 · 401 |

Réponse attendue pour **#13** :

```jsonc
// POST /api/v1/auth/login  { "email": "...", "password": "..." }
{
  "success": true,
  "message": "Connexion réussie.",
  "data": {
    "token": "106|XoYyZ...",                 // personal access token, abilities: ["admin"]
    "expiresAt": "2026-08-29T10:00:00.000000Z",
    "user": { "id": "01J...", "name": "Hermann FOMO", "email": "admin@hermannfomo.dev" }
  }
}
// Identifiants invalides → 401 { "success": false, "message": "Identifiants incorrects." }
```

Implémentation clé : `$request->user()->createToken('admin-dashboard', ['admin'])`,
expiration lue dans `config/sanctum.php` via la variable d'environnement
`SANCTUM_TOKEN_EXPIRATION`, révocable par `$request->user()->currentAccessToken()->delete()`.

**Admin — projets & blog**

| # | Méthode | URI | Action | FormRequest | Resource | Codes |
|---|---|---|---|---|---|---|
| 16 | GET | `/api/v1/admin/projects` | `index` | — | `[ProjectResource]` | 200 · 401 |
| 17 | POST | `/api/v1/admin/projects` | `store` | `StoreProjectRequest` | `ProjectResource` | **201** · 401 · 403 · 422 |
| 18 | GET | `/api/v1/admin/projects/{project}` | `show` | — | `ProjectResource` | 200 · 404 |
| 19 | PUT/PATCH | `/api/v1/admin/projects/{project}` | `update` | `UpdateProjectRequest` | `ProjectResource` | 200 · 404 · 422 |
| 20 | DELETE | `/api/v1/admin/projects/{project}` | `destroy` | — | — | **204** · 404 |
| 21–26 | idem | `/api/v1/admin/blog-posts[...]` | CRUD articles | `Store/UpdateBlogPostRequest` | `BlogPostResource` | idem |

Règles métier projets/articles :

- **slug** : généré côté serveur avec `Str::slug($title)` si absent ; unicité stricte
  (`unique:blog_posts,slug` / `unique:projects,slug`, en ignorant la ressource courante sur update).
  Conflit d'unicité → **422** champ `slug`.
- **articles** : si `coverType = video` alors `coverVideoUrl` requis ; si `youtube` alors
  `coverYoutubeUrl` requis et validé par la règle `YoutubeUrl` (section 6.5).
- **suppression projet/article** : suppression directe (pas de soft delete dans ce périmètre).

**Admin — taxonomies**

| # | Méthode | URI | Notes | Codes |
|---|---|---|---|---|
| 27 | GET | `/api/v1/admin/project-categories` | liste brute | 200 |
| 28 | POST | `/api/v1/admin/project-categories` | `{ label }` unique → 201 | 201 · 422 |
| 29 | PUT/PATCH | `/api/v1/admin/project-categories/{projectCategory}` | `{ label }` | 200 · 404 · 422 |
| 30 | DELETE | `/api/v1/admin/project-categories/{projectCategory}` | **409 si ≥ 1 projet lié** | 204 · **409** · 404 |
| 31–34 | idem | `/api/v1/admin/tech-categories[...]` | même contrat avec `{ name }` | idem |
| 35 | GET | `/api/v1/admin/technologies` | ?categoryId= | 200 |
| 36 | POST | `/api/v1/admin/technologies` | `{ name, categoryId, imageUrl? }` → 201 | 201 · 422 |
| 37 | PUT/PATCH | `/api/v1/admin/technologies/{technology}` | idem partiel | 200 · 404 · 422 |
| 38 | DELETE | `/api/v1/admin/technologies/{technology}` | suppression simple | 204 · 404 |

Garde-fou de suppression d'une catégorie (identique à la logique de `/admin/categories`
du frontend) — renvoyer exactement :

```jsonc
// DELETE /api/v1/admin/project-categories/<id> alors que 3 projets l'utilisent
HTTP 409
{
  "success": false,
  "message": "Impossible de supprimer cette catégorie : elle est utilisée par 3 projet(s).",
  "data": { "usages": 3 }
}
```

**Admin — médias, messages, contenu**

| # | Méthode | URI | Notes | Codes |
|---|---|---|---|---|
| 39 | GET | `/api/v1/admin/media` | bibliothèque complète | 200 |
| 40 | POST | `/api/v1/admin/media` | multipart `file` (§6.6) → `MediaItemResource` | **201** · 401 · 413 · 415 · 422 |
| 41 | DELETE | `/api/v1/admin/media/{mediaItem}` | supprime fichier + ligne | 204 · 404 |
| 42 | GET | `/api/v1/admin/messages` | paginé 15/page, filtres `read=true/false`, `q=` (nom/sujet/email) | 200 |
| 43 | PATCH | `/api/v1/admin/messages/{message}/read` | corps `{ "read": true }` | 200 · 404 · 422 |
| 44 | DELETE | `/api/v1/admin/messages/{message}` | suppression définitive | 204 · 404 |
| 45 | PATCH | `/api/v1/admin/site-content/{section}` | fusion du patch (§2.3), section inconnue → 404 | 200 · 400 · 404 · 422 |
| 46 | PUT | `/api/v1/admin/social-links` | remplacement complet du tableau (§6.7) | 200 · 422 |
| 47 | PUT | `/api/v1/admin/experiences/{experience}` | édition unitaire | 200 · 404 · 422 |
| 48 | PUT | `/api/v1/admin/education/{education}` | édition unitaire | 200 · 404 · 422 |

Réponse type d'une mise à jour réussie (#45) :

```jsonc
// PATCH /api/v1/admin/site-content/hero   { "ctaLabel": "Discutons !" }
{ "success": true, "message": "Contenu mis à jour.", "data": { ...HeroContent complet fusionné } }
```

> **#46** reçoit le tableau complet `[{ label, url, iconName }, ...]`, remplace toutes les
> lignes de `social_links` (transaction + régénération des ULID), et renvoie la nouvelle
> liste `[SocialLinkResource]`. C'est le comportement de `updateSocialLinks(links)` du store.
>
> **#40** — erreur de taille → 413, type MIME refusé → 415 (voir checklist sécurité §7.4).

---

## 6. Validation serveur (FormRequests)

Transposition **champ par champ** des schémas Joi du frontend. Les messages français sont
**obligatoirement identiques** (ils s'affichent tels quels dans l'admin). Syntaxe Laravel 12
(`use Illuminate\Validation\Rule;`), messages via la méthode `messages()` de chaque FormRequest.

Règle commune `HostedImageOrMediaPath` (remplace le `optionalImage` Joi — **plus de dataURL
côté API**, l'upload passe par POST /media) : accepte une URL http(s) **ou** un chemin
commençant par `/storage/`. Message : `L'image doit être un média hébergé ou une URL valide (http/https)`.

### 6.1 `StoreContactMessageRequest` (route #12 — source : `contact.schema.ts`)

| Champ | Règles | Messages |
|---|---|---|
| `name` | `required, string, max:120` | « Votre nom est requis » |
| `email` | `required, email:rfc, max:180` | message Laravel par défaut traduit |
| `subject` | `required, string, max:200` | « Le sujet est requis » |
| `message` | `required, string, min:10, max:2000` | « Votre message est requis » · « Votre message doit contenir au moins 10 caractères » · « Votre message ne doit pas dépasser 2000 caractères » |

### 6.2 `LoginRequest` (route #13)

| Champ | Règles |
|---|---|
| `email` | `required, email` |
| `password` | `required, string, min:8` |

### 6.3 `StoreProjectRequest` / `UpdateProjectRequest` (source : `projectFormSchema`)

| Champ | Règles | Messages |
|---|---|---|
| `title` | `required, string, max:150` | « Le titre est requis » |
| `role` | `required, string, max:120` | « Le rôle est requis » |
| `description` | `required, string, min:10, max:2000` | « La description est requise » · « La description doit contenir au moins 10 caractères » |
| `longDescription` | `nullable, string, max:10000` | — |
| `category` | `required, string, size:26, exists:project_categories,id` | « La catégorie est requise » |
| `imageUrl` | `nullable, new HostedImageOrMediaPath` | message de la règle |
| `demoUrl` | `nullable, url, max:500` | « L'URL doit être valide (http/https) » |
| `githubUrl` | `nullable, url, max:500` | idem |
| `techStack` | `required, array, min:1, max:30` | « Sélectionnez au moins une technologie » |
| `techStack.*` | `required, string, max:100, distinct` | — |
| `featured` | `sometimes, boolean` | — |
| `startDate` | `required, date, format:Y-m-d` | — |
| `endDate` | `nullable, date, format:Y-m-d, after_or_equal:startDate` | — |

`UpdateProjectRequest` = mêmes règles avec `sometimes` + `Rule::unique('projects','slug')` si un
champ `slug` est fourni explicitement. Le `category` reçu (id ULID) alimente la colonne
`project_category_id` dans le contrôleur.

### 6.4 `StoreTechnologyRequest` / catégories (source : `technologyFormSchema`)

| Champ | Règles | Messages |
|---|---|---|
| `name` | `required, string, max:100, unique:technologies,name` (update : ignore id) | « Le nom est requis » · « Cette technologie existe déjà » |
| `categoryId` | `required, string, exists:tech_categories,id` | « La catégorie est requise » |
| `imageUrl` | `nullable, new HostedImageOrMediaPath` | — |

Catégories : `project-categories` → `{ label: required, string, max:100, unique:project_categories,label }` ;
`tech-categories` → `{ name: required, string, max:100, unique:tech_categories,name }`.
Messages : « Le libellé est requis » / « Le nom est requis » · « Cette catégorie existe déjà ».

### 6.5 `StoreBlogPostRequest` / `UpdateBlogPostRequest` (source : `postFormSchema`)

| Champ | Règles | Messages |
|---|---|---|
| `title` | `required, string, max:200` | « Le titre est requis » |
| `slug` | `sometimes, nullable, regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/ , max:255, unique:blog_posts,slug` | « Slug invalide : minuscules et tirets uniquement (ex: mon-article) » · « Ce slug est déjà utilisé » |
| `excerpt` | `required, string, min:10, max:500` | « L'extrait est requis » · « L'extrait doit contenir au moins 10 caractères » |
| `coverType` | `required, in:image,video,youtube` | « Type de couverture invalide » |
| `coverImageUrl` | `nullable, new HostedImageOrMediaPath` | — |
| `coverVideoUrl` | `nullable, url, max:500` · **`required_if:coverType,video`** | « Le lien de la vidéo est requis pour une couverture vidéo » · « L'URL de la vidéo doit être valide (http/https) » |
| `coverYoutubeUrl` | `nullable` · **`required_if:coverType,youtube`** · `new YoutubeUrl` | « Le lien YouTube est requis pour une couverture YouTube » · « Lien YouTube invalide (ex: https://www.youtube.com/watch?v=...) » |
| `tags` | `required, array, min:1, max:15` | « Indiquez au moins un tag » |
| `tags.*` | `required, string, max:50, distinct` | — |
| `readTimeMinutes` | `required, integer, min:1, max:120` | « Temps de lecture requis » · « Minimum 1 minute » · « Maximum 120 minutes » |
| `content` | `required, array, min:1` + structure de blocs ci-dessous | « Le contenu est requis » |

Structure des blocs `content.*` (validation par tableau de règles) :

```php
'content'    => ['required', 'array', 'min:1', 'max:300'],
'content.*.type'     => ['required', 'in:paragraph,heading,quote,code'],
'content.*.text'     => ['required', 'string', 'max:10000'],
'content.*.author'   => ['nullable', 'string', 'max:120'],   // quote uniquement
'content.*.language' => ['nullable', 'string', 'max:30'],   // code uniquement
```

**Règle `YoutubeUrl`** (miroir de `getYoutubeVideoId` du frontend — `src/utils/media.ts`) :
accepte `youtube.com/watch?v=ID`, `youtu.be/ID`, `youtube.com/shorts/ID`,
`youtube.com/embed/ID` (ID = `[A-Za-z0-9_-]{11}`). Le contrôleur stocke l'**URL brute**
reçue (le frontend recalcule l'URL d'embed).

### 6.6 `StoreMediaRequest` (route #40)

```php
'file' => ['required', 'file', 'max:3072',   // 3 Mo — aligné sur MAX_UPLOAD_BYTES du frontend
           'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/svg+xml'],
```

Le service vérifie en plus le **contenu réel** (`$file->getMimeType()` + `getimagesize()`
pour les raster) et assainit les SVG (suppression `<script>`, handlers `on*`, `foreignObject`
— ex. via `enshrined/svg-sanitize`). Nommage : `media/<ulid>.<extension canonique>`.

### 6.7 `UpdateSocialLinksRequest` (route #46) & contenu du site (#45)

```php
// social-links : remplacement complet
'links'           => ['required', 'array', 'min:1', 'max:20'],
'links.*.label'    => ['required', 'string', 'max:100'],
'links.*.url'      => ['required', 'url', 'max:500'],
'links.*.iconName' => ['required', 'string', 'max:50'],   // nom lucide résolu côté frontend

// site-content/{section} : le corps EST le patch (objet libre)
// Validation : 'data' => ['required', 'array'] + garde-fou par section dans le contrôleur
// (liste blanche des clés attendues par section, cf. interfaces TS — toute clé inconnue est rejetée 422).
```

Sections acceptées (#45) : `profile`, `hero`, `skills`, `homeSections`, `pages`, `footer` —
toute autre valeur de `{section}` → **404**.

### 6.8 `UpdateMessageReadRequest` (route #43)

`read` → `required, boolean`.

### 6.9 `UpdateExperienceRequest` / `UpdateEducationRequest` (#47, #48)

| Champ | Règles communes |
|---|---|
| `company`/`school`, `position`/`degree` | `required, string, max:150` |
| `description` | `required` (experience) / `nullable` (education), `string, max:5000` |
| `location` | `nullable, string, max:150` (experience) |
| `startDate` | `required, date, format:Y-m-d` |
| `endDate` | `nullable, date, format:Y-m-d, after_or_equal:startDate` |
| `sortOrder` | `sometimes, integer, min:0` |

---

## 7. Sécurité — checklist impérative

### 7.1 Transport & en-têtes

- [ ] **HTTPS obligatoire** en production : `URL::forceScheme('https')` (AppServiceProvider
      quand `app()->isProduction()`), redirection 80→443 au niveau du serveur web.
- [ ] `TrustProxies` configuré (derrière Nginx/Cloudflare) : `$middleware->trustProxies(at: '*')`
      restreint aux IPs du reverse proxy.
- [ ] En-têtes ajoutés par un middleware maison `SecureHeaders` (append à `api` et `web`) :
      `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`,
      `Strict-Transport-Security: max-age=31536000; includeSubDomains` (prod), permissions-policy minimal.

### 7.2 CORS & JSON

- [ ] `config/cors.php` : `allowed_origins = [env('FRONTEND_URL')]` — **jamais `*`** ;
      `allowed_methods = ['*']` ; `allowed_headers = ['Content-Type', 'Authorization', 'Accept']` ;
      `supports_credentials = false` (Bearer, pas de cookies) ; `paths = ['api/*']`.
- [ ] Middleware `ForceJsonResponse` en tête du groupe `api` : force `Accept: application/json`
      → aucune page d'erreur HTML ne fuite vers le frontend.

### 7.3 Authentification Sanctum

- [ ] Tokens **hachés** : `Sanctum::authenticatePersonalAccessTokensUsing` ou option
      `hash: true` dans `config/sanctum.php` + `personal_access_token` hashé à l'émission.
- [ ] **Expiration** : `SANCTUM_TOKEN_EXPIRATION` (7 jours) — token expiré → 401.
- [ ] Ability unique `admin` à l'émission ; groupe admin protégé par
      `['auth:sanctum', 'ability:admin']` (403 si ability absente).
- [ ] **Logout révoque** le token courant ; aucune liste de tokens exposée.
- [ ] Login : throttle 5/min par IP+email (§5.1), comparaison via le guard standard
      (hachage bcrypt), réponse **identique** pour « email inconnu » et « mot de passe faux »
      (pas d'énumération de comptes).
- [ ] Aucune route `register` / `forgot-password` exposée dans ce périmètre.
- [ ] Recommandation transmise au frontend : stocker le token dans un cookie `httpOnly`
      posé par une route BFF Next.js, jamais dans `localStorage`.

### 7.4 Uploads de médias

- [ ] Taille max **3 Mo** (`max:3072`), limite aussi posée à Nginx (`client_max_body_size 4m`).
- [ ] Whitelist MIME stricte (jpeg, png, webp, gif, svg) **et** vérification du contenu réel
      (`getimagesize`) — jamais la seule extension.
- [ ] SVG **assainis** (cf. §6.6) ou interdits si la librairie n'est pas installée.
- [ ] Noms **générés** (`media/<ulid>.<ext>`), jamais le nom original ; disque `public`
      standard Laravel (fichiers servis par Nginx depuis `public/storage`, aucun `.php`
      exécutable dans l'arborescence).
- [ ] Suppression média = suppression du fichier physique + ligne DB (MediaService).
- [ ] Upload réservé à l'admin (groupe `auth:sanctum`), jamais de route publique d'upload.

### 7.5 Données & requêtes

- [ ] **`$fillable` explicites** sur chaque modèle — anti mass-assignment.
- [ ] Toutes les requêtes passent par Eloquent / query builder (anti injection SQL) ;
      aucun `whereRaw` avec entrée utilisateur.
- [ ] Liaison de route par ULID (`->parameters([...])`) — pas d'énumération d'IDs séquentiels.
- [ ] Sortie : le frontend React échappe déjà le HTML ; l'API ne stocke jamais de HTML brut
      (le `content` du blog est un tableau de blocs texte, pas du markdown/HTML exécutable).
- [ ] Validation **systématique** via FormRequest avec `authorize()` retournant `true`
      (l'accès est déjà filtré par le middleware) — jamais de `Validator::make` éparpillé.

### 7.6 Configuration & exploitation

- [ ] `APP_DEBUG=false`, `APP_ENV=production`, `APP_KEY` fort (`php artisan key:generate`),
      `.env` **jamais commité** (`.gitignore` standard Laravel).
- [ ] Compte admin créé uniquement par le seeder depuis `ADMIN_EMAIL`/`ADMIN_PASSWORD`
      (variables d'environnement du serveur, mot de passe ≥ 12 caractères).
- [ ] `php artisan down` pendant les migrations sensibles ; sauvegarde DB avant déploiement.
- [ ] Logs : canal `daily`, aucune donnée sensible (jamais de mot de passe/token en log).
- [ ] Vérifications post-déploiement : `php artisan route:list` (aucune route admin hors
      middleware), `php artisan about`, test manuel d'une route admin sans token → **401**.

### 7.7 Rate limiting (récapitulatif)

| Limiteur | Cible | Limite |
|---|---|---|
| `api` | toutes les routes publiques | 60/min/IP |
| `contact` | POST /contact | 5/min/IP |
| `auth` | POST /auth/login | 5/min/IP+email |
| `admin` | routes /admin | 120/min/token |

---

## 8. Seeders

**Source de vérité : `src/mocks/portfolio.mock.ts`** du repo frontend. Le seeder reproduit
fidèlement ses exports (mêmes slugs, mêmes textes, mêmes ordres). L'agent lit ce fichier et
le convertit en seeders PHP.

```bash
php artisan make:seeder DatabaseSeeder
php artisan make:seeder {AdminUserSeeder,TaxonomySeeder,ProjectSeeder,BlogPostSeeder,SiteContentSeeder,PortfolioSeeder}
php artisan migrate:fresh --seed
```

Exigences :

1. **Idempotent** : `DatabaseSeeder` tronque les tables dans l'ordre inverse des FK
   (`DB::statement('SET FOREIGN_KEY_CHECKS=0')` → truncate → réactivation) avant de peupler.
2. **AdminUserSeeder** : un seul utilisateur, `email = env('ADMIN_EMAIL')`,
   `password = Hash::make(env('ADMIN_PASSWORD'))`. Échouer avec un message clair si la
   variable est vide — jamais de mot de passe par défaut en dur.
3. **TaxonomySeeder** : les 4 catégories de projets du mock (`projectCategories`) puis les 6
   catégories de technologies (`techCategories` : frontend, backend, mobile, database,
   devops, outils) et leurs ~30 technologies (`technologies`) avec `image_url = null`
   (les icônes de marque restent gérées côté frontend).
4. **ProjectSeeder** : les 6 projets du mock, slugs exacts (`vtc-control`, `y-pay-wallet`,
   `whatsapp-business`, …), `tech_stack` identique aux seeds.
5. **BlogPostSeeder** : les 4 articles du mock, slugs exacts, dont le coverType `youtube`
   (b3) et le coverType `video` (b4) ; `content` = blocs typés conformes à §3.4.
6. **SiteContentSeeder** : une ligne par section (`profile`, `hero`, `skills`,
   `homeSections`, `pages`, `footer`) depuis l'objet exporté `siteContent` du mock —
   textes exacts.
7. **PortfolioSeeder** : expériences, formations, liens sociaux (GitHub, LinkedIn,
   Twitter/X, Mail), médiathèque (les 10 médias Unsplash des seeds).

Vérification immédiate après seed :

```bash
curl -s http://localhost:8000/api/v1/site-content | jq '.data.profile.fullName'
curl -s http://localhost:8000/api/v1/projects | jq '.data | length'    # → 6
curl -s http://localhost:8000/api/v1/blog-posts | jq '.data | length'  # → 4
```

---

## 9. Tests & vérification finale

### 9.1 Jeu de tests feature (Pest recommandé)

Un test par famille, couvrant tous les codes de la section 4.2 :

| Famille | Cas obligatoires |
|---|---|
| Public lecture | chaque GET public → 200 + enveloppe conforme ; slug inconnu projet/article → 404 ; filtres `?featured=1`, `?category=`, `?tag=` |
| Contact | POST valide → 201 + ligne en base avec `read_at IS NULL` ; payload invalide → 422 messages FR ; 6e appel dans la minute → 429 |
| Auth | login OK → 200 + token utilisable ; mauvais mot de passe → 401 identique à email inconnu ; `/api/v1/admin/*` sans token → 401 ; token sans ability → 403 ; après logout → 401 |
| CRUD admin | create/update/delete sur projects, blog-posts, technologies, catégories ×2, media, messages, site-content, social-links → 201/200/204 |
| Garde-fous | DELETE catégorie de projet référencée → **409** avec `usages` ; idem tech-category ; PATCH section inconnue → 404 |
| Médias | upload jpg/png/webp → 201 ; fichier > 3 Mo → 413 ; `.txt` renommé `.jpg` → 415 ; DELETE → fichier physique supprimé |

### 9.2 Exemple de référence (Pest)

```php
it('refuse la suppression d\'une catégorie de projet utilisée', function () {
    $admin = User::factory()->create();
    $cat   = ProjectCategory::create(['label' => 'Web']);
    Project::factory()->count(2)->create(['project_category_id' => $cat->id]);
    $token = $admin->createToken('test', ['admin'])->plainTextToken;

    $response = $this->deleteJson("/api/v1/admin/project-categories/{$cat->id}", [], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertStatus(409)
             ->assertJsonPath('success', false)
             ->assertJsonPath('data.usages', 2);
});
```

### 9.3 Checklist de vérification finale (dans l'ordre)

1. `php artisan migrate:fresh --seed` — zéro erreur.
2. `php artisan test` — tous verts.
3. `php artisan route:list` — toutes les routes admin sont sous `auth:sanctum` + `ability:admin`.
4. Curl manuel :
   - `GET /api/v1/site-content` → arbre complet ;
   - `POST /api/v1/contact` → 201, puis visible via login + `GET /api/v1/admin/messages` ;
   - toute route `/api/v1/admin/*` sans token → **401** ;
   - `DELETE /api/v1/admin/project-categories/<id_utilisée>` → **409** ;
   - dépassement du throttle contact → **429**.
5. Upload réel d'une image via `POST /api/v1/admin/media` (multipart) → URL publique affichable dans un `<img>`.
6. Production : HTTPS actif, `APP_DEBUG=false`, headers présents
   (`curl -I https://backend.hermannfomo.dev/api/v1/site-content`).

---

## 10. Annexes

### Annexe A — Correspondance champ TS ↔ colonne MySQL ↔ JSON

| Modèle TS | Table | Colonnes (snake_case) | Sortie JSON (camelCase) |
|---|---|---|---|
| `Project` | `projects` | slug, title, role, description, long_description, image_url, tech_stack, project_category_id, demo_url, github_url, featured, start_date, end_date | idem + `links:{demoUrl,githubUrl}` ; `category` = id catégorie |
| `BlogPost` | `blog_posts` | slug, title, excerpt, cover_image_url, cover_type, cover_video_url, cover_youtube_url, tags, published_at, read_time_minutes, content | `coverImageUrl`, `coverType`, `publishedAt` (Y-m-d), `readTimeMinutes` |
| `Technology` | `technologies` | name, tech_category_id, image_url | `categoryId`, `imageUrl` |
| `TechCategory` | `tech_categories` | name | idem |
| `ProjectCategory` | `project_categories` | label | idem |
| `ContactMessage` | `contact_messages` | name, email, subject, message, read_at, created_at | `sentAt` = created_at ISO-8601, `read` = bool |
| `MediaItem` | `media_items` | name, disk, path, mime_type, size | `src` = URL absolue |
| `Experience` | `experiences` | company, position, description, location, start_date, end_date, sort_order | camelCase direct |
| `Education` | `education` | school, degree, description, start_date, end_date, sort_order | camelCase direct |
| `SocialLink` | `social_links` | label, url, icon_name, sort_order | `iconName` |
| `SiteContent.*` | `site_content` | section (PK), data | arbre identique aux interfaces TS |

### Annexe B — Mapping des `TODO backend.hermannfomo.dev` du frontend

| Emplacement frontend | Endpoint(s) à implémenter |
|---|---|
| `src/stores/usePortfolioStore.ts` (en-tête) | ensemble des GET publics + CRUD admin (#1–#48) |
| `ContactSection.tsx` onSubmit | POST `/api/v1/contact` (#12) |
| `usePortfolioStore.addMedia` / `ImagePicker.tsx` import local | POST `/api/v1/admin/media` (#40) |
| `portfolio.schema.ts` `optionalImage` (dataURL acceptés) | remplacer par le chemin retourné par #40 |
| `[slug]/page.tsx` projets & blog (`generateMetadata`) | métadonnées dynamiques via #3 / #5 |
| Boutons CV (`Navbar`, page À propos) | URL servie par `siteContent.profile.cvUrl` (#45) |

### Annexe C — Évolutions prévues (hors périmètre v1)

Ne pas implémenter maintenant, mais ne pas rendre impossible : pagination publique,
i18n (contenus traduits), rotation/renouvellement des tokens, soft deletes,
statut brouillon/publié pour les articles, anti-spam avancé (hCaptcha) sur /contact.

---

*Fin de la spécification — document généré depuis le frontend livré (branche
`Feature/WhatsappBusiness`, août 2026). Toute divergence entre ce contrat et le code
frontend fait foi : c'est `portfolio.type.ts` qui définit la vérité.*





