<?php

namespace App\Services;

use App\Models\SiteContent;
use Illuminate\Validation\ValidationException;

/**
 * Gère la table site_content : une ligne par section, data JSON.
 * Le PATCH reproduit updateSiteContent(section, patch) du store zustand
 * (fusion de premier niveau ; skills et homeSections.* remplacés en bloc).
 */
class SiteContentService
{
    /** Sections acceptées et clés attendues dans le patch (interfaces TS). */
    private const SECTIONS = [
        'profile' => [
            'fullName', 'firstName', 'lastName', 'logoInitials',
            'roleLine', 'techLine', 'location', 'email', 'cvUrl',
        ],
        'hero' => ['availabilityBadge', 'description', 'ctaLabel', 'scrollHint', 'stats'],
        'skills' => null, // tableau SkillCard[] — remplacé en bloc
        'homeSections' => ['about', 'technologies', 'projects', 'contact'],
        'pages' => ['about', 'projects', 'blog'],
        'footer' => ['baseline', 'copyrightPrefix', 'copyrightSuffix'],
    ];

    /**
     * Arbre SiteContent complet (lecture publique).
     */
    public function tree(): array
    {
        $sections = SiteContent::query()->pluck('data', 'section');

        $tree = [];

        foreach (array_keys(self::SECTIONS) as $section) {
            $tree[$section] = $sections[$section] ?? ($section === 'skills' ? [] : []);
        }

        return $tree;
    }

    /**
     * Fusionne un patch sur une section puis retourne la section fusionnée.
     *
     * @param  array<string, mixed>  $patch
     * @return array<string, mixed>
     *
     * @throws ValidationException Clé inconnue pour cette section
     */
    public function patchSection(string $section, array $patch): array
    {
        // Section inconnue → 404 (géré par l'appelant via abort_if)
        abort_unless(array_key_exists($section, self::SECTIONS), 404);

        // Garde-fou : liste blanche des clés par section
        $allowed = self::SECTIONS[$section];

        if ($allowed === null) {
            // « skills » : tableau SkillCard[] remplacé en bloc
            if (! array_is_list(array_values($patch))) {
                throw ValidationException::withMessages([
                    'skills' => ['La section "skills" doit être un tableau de cartes de compétences.'],
                ]);
            }

            $merged = array_values($patch);
        } else {
            $unknown = array_diff(array_keys($patch), $allowed);

            if ($unknown !== []) {
                throw ValidationException::withMessages([
                    implode(', ', $unknown) => ['Clé(s) non autorisée(s) pour la section "'.$section.'".'],
                ]);
            }

            // {...sectionActuelle, ...patch} — même sémantique que le store zustand
            $merged = array_merge(SiteContent::find($section)?->data ?? [], $patch);
        }

        SiteContent::updateOrCreate(
            ['section' => $section],
            ['data' => $merged]
        );

        return $merged;
    }
}
