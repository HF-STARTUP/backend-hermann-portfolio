<?php

namespace Tests\Feature;

use App\Models\ProjectCategory;
use App\Models\TechCategory;

/**
 * Garde-fou de suppression des catégories référencées → 409 + data.usages.
 */
class CategoryGuardTest extends ApiTestCase
{
    public function test_delete_categorie_projets_referencee_409_avec_usages(): void
    {
        $id = ProjectCategory::query()->where('label', 'Web')->value('id');

        $response = $this->adminDelete('/api/v1/admin/project-categories/'.$id);

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.usages', 3);
        $m = (string) $response->json('message');
        $this->assertStringContainsString('utilisée par 3 projet(s)', $m);

        // La catégorie existe toujours
        $this->assertDatabaseHas('project_categories', ['id' => $id]);
    }

    public function test_delete_categorie_tech_referencee_409_avec_usages(): void
    {
        $frontendId = TechCategory::query()->where('name', 'Frontend')->value('id');

        // Frontend porte 6 technologies
        $response = $this->adminDelete('/api/v1/admin/tech-categories/'.$frontendId);

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.usages', 6);
        $this->assertStringContainsString('utilisée par 6 technologie(s)',
            (string) $response->json('message'));
    }

    public function test_delete_categorie_non_referencee_reussit(): void
    {
        // Toutes les catégories seedées ont au moins un projet : on en crée une vierge
        $created = $this->adminPost('/api/v1/admin/project-categories', ['label' => 'Catégorie vierge']);
        $created->assertStatus(201);
        $id = (string) $created->json('data.id');

        $this->adminDelete('/api/v1/admin/project-categories/'.$id)->assertNoContent();
        $this->assertDatabaseMissing('project_categories', ['id' => $id]);
    }
}
