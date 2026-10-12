# Backend Updates — Certifications & Photos sur la page À propos

> **Scope** : modifications à appliquer sur le backend Laravel existant. Les routes #47/#48
> (PUT experiences/education) sont déjà implémentées — elles sont remplacées par l'`apiResource`
> ci-dessous. Le reste est **additif**.

---

## 1. Nouvelle table `certifications` (migration)

```php
// database/migrations/xxxx_xx_xx_create_certifications_table.php
Schema::create('certifications', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('name', 200);
    $table->string('issuer', 150);
    $table->text('description')->nullable();
    $table->date('date_obtained');          // Y-m-d
    $table->string('url', 500)->nullable();
    $table->string('image_url', 500)->nullable();
    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->timestamps();
    $table->index('sort_order');
});
```

> **⚠️ Dates** : `date_obtained` est un `DATE` réel (format `Y-m-d`).
> Contrairement aux expériences/formations qui utilisent `VARCHAR(7)` (`YYYY-MM`),
> les certifications utilisent une date complète.

---

## 2. Nouveau modèle

```php
// app/Models/Certification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certification extends Model
{
    use \Illuminate\Support\Traits\HasUlids;

    protected $fillable = ['name', 'issuer', 'description', 'date_obtained',
                          'url', 'image_url', 'sort_order'];

    protected function casts(): array
    {
        return [
            'date_obtained' => 'date',
            'sort_order'    => 'integer',
        ];
    }
}
```

---

## 3. Nouvelle Resource

```php
// app/Http/Resources/CertificationResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\MediaService;

class CertificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'issuer'       => $this->issuer,
            'description'  => $this->description,
            'dateObtained' => $this->date_obtained->format('Y-m-d'),
            'url'          => $this->url,
            'imageUrl'     => $this->image_url
                ? MediaService::absoluteUrl($this->image_url)
                : null,
            'sortOrder'    => $this->sort_order,
        ];
    }
}
```

---

## 4. Nouveaux FormRequests

### StoreCertificationRequest

```php
// app/Http/Requests/Certification/StoreCertificationRequest.php
public function rules(): array
{
    return [
        'name'          => ['required', 'string', 'max:200'],
        'issuer'        => ['required', 'string', 'max:150'],
        'description'   => ['nullable', 'string', 'max:5000'],
        'dateObtained'  => ['required', 'date', 'date_format:Y-m-d'],
        'url'           => ['nullable', 'url', 'max:500'],
        'imageUrl'      => ['nullable', new HostedImageOrMediaPath],
        'sortOrder'     => ['integer', 'min:0', 'default:0'],
    ];
}

public function messages(): array
{
    return [
        'name.required'        => 'Ce champ est requis',
        'issuer.required'      => 'Ce champ est requis',
        'dateObtained.required' => 'Ce champ est requis',
        'dateObtained.date_format' => 'Format attendu : YYYY-MM-DD',
    ];
}
```

### UpdateCertificationRequest — identique mais `sometimes` sur tous les champs

---

## 5. Nouveau contrôleur

```php
// app/Http/Controllers/Api/V1/Admin/CertificationController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Certification\StoreCertificationRequest;
use App\Http\Requests\Certification\UpdateCertificationRequest;
use App\Http\Resources\CertificationResource;
use App\Models\Certification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $certifications = Certification::orderBy('sort_order')->get();
        return $this->success(
            CertificationResource::collection($certifications)
        );
    }

    public function store(StoreCertificationRequest $request)
    {
        $cert = Certification::create($request->validated());
        return $this->success(
            new CertificationResource($cert),
            'Certification créée avec succès.',
            201
        );
    }

    public function update(UpdateCertificationRequest $request, Certification $certification)
    {
        $certification->update($request->validated());
        return $this->success(
            new CertificationResource($certification),
            'Certification mise à jour.'
        );
    }

    public function destroy(Certification $certification)
    {
        $certification->delete();
        return response()->noContent();  // 204
    }
}
```

---

## 6. Routes à ajouter

Dans le groupe admin (`/api/v1/admin`, middleware `auth:sanctum` + `ability:admin`) :

```php
Route::apiResource('certifications', CertificationController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->parameters(['certifications' => 'certification']);
```

| # | Méthode | URI | Action | Codes |
|---|---|---|---|---|
| 49 | GET | `/api/v1/certifications` | `TimelineController@certifications` (public) | 200 |
| 58 | GET | `/api/v1/admin/certifications` | `CertificationController@index` | 200 · 401 · 403 |
| 59 | POST | `/api/v1/admin/certifications` | `CertificationController@store` | **201** · 401 · 403 · 422 |
| 60 | PUT/PATCH | `/api/v1/admin/certifications/{certification}` | `CertificationController@update` | 200 · 401 · 403 · 404 · 422 |
| 61 | DELETE | `/api/v1/admin/certifications/{certification}` | `CertificationController@destroy` | **204** · 401 · 403 · 404 |

---

## 7. Route publique `GET /certifications`

Ajoutez à `TimelineController` (ou un `CertificationController` public) :

```php
// Public\TimelineController.php
public function certifications()
{
    $certs = Certification::orderBy('sort_order')->get();
    return CertificationResource::collection($certs);
}
```

```php
// routes/api.php — groupe public
Route::get('certifications', [Public\TimelineController::class, 'certifications']);
```

---

## 8. SiteContentResource — ajouter les certifications

Le `SiteContentResource` (sert à `GET /api/v1/site-content`) doit inclure un tableau
`certifications` au niveau racine, comme `skills` :

```php
// SiteContentResource.php
public function toArray($request): array
{
    return [
        'profile'     => ProfileContent::make(...),
        'hero'        => HeroContent::make(...),
        'skills'      => SkillCardResource::collection(...),
        'homeSections'=> ...,
        'pages'       => ...,
        'certifications' => CertificationResource::collection(
            Certification::orderBy('sort_order')->get()
        ),
        'footer'      => ...,
    ];
}
```

> Les certifications sont **dans leur propre table** — pas une section `site_content`.

---

## 9. PATCH site-content/pages — nouveaux champs `about`

La route `PATCH /api/v1/admin/site-content/pages` (#45) accepte déjà un patch profond.
Ajouter la validation des 3 nouveaux champs dans `pages.about` :

| Champ | Règles |
|---|---|
| `about.certificationTitle` | `sometimes, string, max:100` |
| `about.photoDesktopUrl` | `sometimes, nullable, string, max:500, new HostedImageOrMediaPath` |
| `about.photoMobileUrl` | `sometimes, nullable, string, max:500, new HostedImageOrMediaPath` |

---

## 10. Seeder — 3 certifications

```php
// database/seeders/CertificationSeeder.php
Certification::insert([
    [
        'id' => Str::ulid(),
        'name' => 'AWS Certified Cloud Practitioner',
        'issuer' => 'Amazon Web Services',
        'description' => "Fondamentaux du cloud AWS : calcul, stockage, sécurité, tarification et architecture distribuée.",
        'date_obtained' => '2023-07-15',
        'url' => 'https://www.credly.com/badges/aws-cloud-practitioner',
        'image_url' => 'https://images.unsplash.com/photo-15505744163-ab1f2b4cb5e8?w=200&q=80',
        'sort_order' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'id' => Str::ulid(),
        'name' => 'Docker Certified Associate',
        'issuer' => 'Docker Inc.',
        'description' => "Conteneurisation, orchestration avec Docker Swarm, gestion des images et des réseaux.",
        'date_obtained' => '2023-09-22',
        'url' => 'https://www.credly.com/badges/docker-certified-associate',
        'image_url' => 'https://images.unsplash.com/photo-1522364722578-520755432815?w=200&q=80',
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'id' => Str::ulid(),
        'name' => 'LPIC-1 Certified Linux Administrator',
        'issuer' => 'Linux Professional Institute',
        'description' => "Administration systèmes Linux : gestion des utilisateurs, paquets, fichiers, automatisation (scripts) et réseaux.",
        'date_obtained' => '2023-11-30',
        'url' => null,
        'image_url' => null,
        'sort_order' => 2,
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);
```

---

## 11. Mise à jour routes existantes (expériences/education)

Les routes PUT existantes #47/#48 sont **remplacées** par `apiResource` :

```php
// AVANT (existe déjà — À RETIRER)
Route::put('/admin/experiences/{experience}', ...);    // #47
Route::put('/admin/education/{education}', ...);       // #48

// APRÈS (apiResource — À AJOUTER)
Route::apiResource('experiences', ExperienceController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->parameters(['experiences' => 'experience']);          // #50–#53

Route::apiResource('education', EducationController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->parameters(['education' => 'education']);              // #54–#57
```

---

## Checklist de vérification

- [ ] Migration `certifications` exécutée
- [ ] Modèle `Certification` avec `HasUlids` + `$fillable` explicite
- [ ] `CertificationResource` → camelCase (`dateObtained`, `imageUrl`, `sortOrder`)
- [ ] `StoreCertificationRequest` / `UpdateCertificationRequest` avec messages FR
- [ ] `CertificationController` (admin) — CRUD complet
- [ ] `GET /api/v1/certifications` (public) → `[CertificationResource]` trié `sort_order` asc
- [ ] `SiteContentResource` inclut `certifications` au niveau racine
- [ ] `PATCH /admin/site-content/pages` accepte `about.certificationTitle`, `about.photoDesktopUrl`, `about.photoMobileUrl`
- [ ] Routes #58–#61 déclarées dans le groupe admin
- [ ] Anciennes routes PUT #47/#48 remplacées par `apiResource`
- [ ] Seeder 3 certifications (données identiques au mock frontend)
- [ ] Tests : CRUD certification → 201/200/204 ; format `dateObtained` invalide → 422