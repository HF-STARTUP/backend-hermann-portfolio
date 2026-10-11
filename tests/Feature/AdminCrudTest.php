<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Experience;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\SocialLink;
use App\Models\TechCategory;
use App\Models\Technology;
use Illuminate\Support\Facades\DB;

/**
 * CRUD admin complet — projects, blog-posts, technologies, messages,
 * site-content, social-links, experiences, education.
 */
class AdminCrudTest extends ApiTestCase
{
    /* ------------------------------------------------------------- */
    /* Projets                                                        */
    /* ------------------------------------------------------------- */

    private function projectPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Nouveau projet',
            'role' => 'Développeur',
            'description' => 'Une description de projet suffisamment longue.',
            'category' => ProjectCategory::query()->where('label', 'Web')->value('id'),
            'techStack' => ['React', 'TypeScript'],
            'featured' => false,
            'startDate' => '2025-01-01',
            'demoUrl' => 'https://demo.example.com',
        ], $overrides);
    }

    public function test_store_project_genere_le_slug_et_renvoie_201(): void
    {
        $response = $this->adminPost('/api/v1/admin/projects', $this->projectPayload());

        $response->assertStatus(201)->assertJsonPath('success', true);
        $data = $response->json('data');
        $this->assertSame('nouveau-projet', $data['slug']);
        $this->assertSame(['React', 'TypeScript'], $data['techStack']);
        $this->assertSame('https://demo.example.com', $data['links']['demoUrl']);

        // Slug unique par suffixation si collision
        $second = $this->adminPost('/api/v1/admin/projects', $this->projectPayload());
        $second->assertStatus(201);
        $this->assertSame('nouveau-projet-2', $second->json('data.slug'));
    }

    public function test_update_et_delete_project(): void
    {
        $projectId = Project::query()->where('slug', 'agora-labs')->value('id');

        $this->adminPut("/api/v1/admin/projects/{$projectId}", $this->projectPayload([
            'title' => 'AgoraLabs CRM v2',
            'slug' => 'agora-labs-v2',
        ]))->assertOk()->assertJsonPath('data.slug', 'agora-labs-v2');

        $this->adminDelete('/api/v1/admin/projects/'.$projectId)->assertNoContent();
        $this->assertDatabaseMissing('projects', ['id' => $projectId]);
    }

    public function test_store_project_tech_stack_vide_est_refuse(): void
    {
        $this->adminPost('/api/v1/admin/projects', $this->projectPayload(['techStack' => []]))
            ->assertStatus(422)
            ->assertJsonPath('errors.techStack.0', 'Sélectionnez au moins une technologie');
    }

    /* ------------------------------------------------------------- */
    /* Blog                                                           */
    /* ------------------------------------------------------------- */

    private function blogPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Mon article de test',
            'excerpt' => 'Un extrait suffisamment long pour la validation.',
            'coverType' => 'image',
            'tags' => ['Test', 'PHPUnit'],
            'publishedAt' => '2026-01-15',
            'readTimeMinutes' => 3,
            'content' => [
                ['type' => 'paragraph', 'text' => 'Premier paragraphe du contenu.'],
                ['type' => 'heading', 'text' => 'Un titre'],
                ['type' => 'quote', 'text' => 'Citation.', 'author' => 'Auteur'],
                ['type' => 'code', 'language' => 'php', 'text' => 'echo "ok";'],
            ],
        ], $overrides);
    }

    public function test_store_blog_post_201_puis_show(): void
    {
        $response = $this->adminPost('/api/v1/admin/blog-posts', $this->blogPayload());

        $response->assertStatus(201);
        $slug = $response->json('data.slug');
        $this->assertSame('mon-article-de-test', $slug);

        // Visible en public avec les blocs typés
        $public = $this->getJson("/api/v1/blog-posts/{$slug}");
        $public->assertOk();
        $this->assertCount(4, $public->json('data.content'));
        $this->assertSame('quote', $public->json('data.content.2.type'));
        $this->assertSame('Auteur', $public->json('data.content.2.author'));
    }

    public function test_blog_cover_youtube_invalide_est_refusee(): void
    {
        $this->adminPost('/api/v1/admin/blog-posts', $this->blogPayload([
            'coverType' => 'youtube',
            'coverYoutubeUrl' => 'https://dailymotion.com/video/x8x',
        ]))->assertStatus(422)
          ->assertJsonPath('errors.coverYoutubeUrl.0',
              'Lien YouTube invalide (ex: https://www.youtube.com/watch?v=...)');
    }

    public function test_blog_slug_duplique_est_refuse(): void
    {
        $this->adminPost('/api/v1/admin/blog-posts', $this->blogPayload(['slug' => 'gerer-etat-complexe-nextjs']))
            ->assertStatus(422)
            ->assertJsonPath('errors.slug.0', 'Ce slug est déjà utilisé');
    }

    public function test_blog_read_time_hors_limites(): void
    {
        $this->adminPost('/api/v1/admin/blog-posts', $this->blogPayload(['readTimeMinutes' => 0]))
            ->assertStatus(422)
            ->assertJsonPath('errors.readTimeMinutes.0', 'Minimum 1 minute');

        $this->adminPost('/api/v1/admin/blog-posts', $this->blogPayload(['readTimeMinutes' => 121]))
            ->assertStatus(422)
            ->assertJsonPath('errors.readTimeMinutes.0', 'Maximum 120 minutes');
    }

    /* ------------------------------------------------------------- */
    /* Technologies                                                   */
    /* ------------------------------------------------------------- */

    public function test_technology_crud_sans_show(): void
    {
        $categoryId = TechCategory::query()->where('name', 'Outils')->value('id');

        $created = $this->adminPost('/api/v1/admin/technologies', [
            'name' => 'Bun',
            'categoryId' => $categoryId,
        ]);
        $created->assertStatus(201)->assertJsonPath('data.name', 'Bun');
        $id = $created->json('data.id');

        $this->adminPut("/api/v1/admin/technologies/{$id}", [
            'name' => 'Bun.sh',
            'categoryId' => $categoryId,
        ])->assertOk()->assertJsonPath('data.name', 'Bun.sh');

        // show n'existe pas → 405
        $this->withToken($this->login())->getJson("/api/v1/admin/technologies/{$id}")->assertStatus(405);

        $this->adminDelete("/api/v1/admin/technologies/{$id}")->assertNoContent();

        // Doublon refusé
        $this->adminPost('/api/v1/admin/technologies', [
            'name' => 'React',
            'categoryId' => TechCategory::query()->where('name', 'Frontend')->value('id'),
        ])->assertStatus(422)
          ->assertJsonPath('errors.name.0', 'Cette technologie existe déjà');
    }

    /* ------------------------------------------------------------- */
    /* Messages                                                       */
    /* ------------------------------------------------------------- */

    public function test_messages_pagination_et_filtres(): void
    {
        $response = $this->adminGet('/api/v1/admin/messages');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'data' => [
                'items' => [['id', 'name', 'email', 'subject', 'message', 'sentAt', 'read']],
                'pagination' => ['currentPage', 'lastPage', 'perPage', 'total'],
            ]]);

        $this->assertSame(3, $response->json('data.pagination.total'));

        // Filtre read=1 : seul m3 (Sophie Ngo) est lu
        $read = $this->adminGet('/api/v1/admin/messages?read=1')->json('data.items');
        $this->assertCount(1, $read);
        $this->assertSame('Sophie Ngo', $read[0]['name']);
        $this->assertTrue($read[0]['read']);

        // Recherche q sur le nom
        $search = $this->adminGet('/api/v1/admin/messages?q=aicha')->json('data.items');
        $this->assertCount(1, $search);
        $this->assertSame('aicha.mbarga@exemple.cm', $search[0]['email']);
    }

    public function test_message_marquer_lu_puis_supprimer(): void
    {
        $id = DB::table('contact_messages')->where('email', 'aicha.mbarga@exemple.cm')->value('id');

        $this->adminPatch("/api/v1/admin/messages/{$id}/read", ['read' => true])
            ->assertOk()
            ->assertJsonPath('data.read', true);
        $this->assertNotNull(DB::table('contact_messages')->find($id)->read_at);

        $this->adminDelete('/api/v1/admin/messages/'.$id)->assertNoContent();
        $this->assertDatabaseCount('contact_messages', 2);
    }

    /* ------------------------------------------------------------- */
    /* Site content                                                   */
    /* ------------------------------------------------------------- */

    public function test_patch_site_content_fusionne_le_premier_niveau(): void
    {
        $response = $this->adminPatch('/api/v1/admin/site-content/hero', [
            'ctaLabel' => 'Découvrir mes réalisations',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Contenu mis à jour.')
            ->assertJsonPath('data.ctaLabel', 'Découvrir mes réalisations');

        // Les autres clés de la section sont intactes (fusion)
        $tree = $this->getJson('/api/v1/site-content')->json('data');
        $this->assertSame('Découvrir mes réalisations', $tree['hero']['ctaLabel']);
        $this->assertSame('Ouvert pour de nouvelles opportunités', $tree['hero']['availabilityBadge']);
        $this->assertCount(4, $tree['hero']['stats']);
    }

    public function test_patch_skills_remplace_le_tableau_en_bloc(): void
    {
        $newSkills = [
            ['id' => 'skill-1', 'name' => 'Une seule carte', 'iconName' => 'Code', 'features' => ['X']],
        ];
        $this->adminPut('/api/v1/admin/site-content/skills', [])->assertStatus(405);

        $response = $this->adminPatch('/api/v1/admin/site-content/skills', $newSkills);
        $response->assertOk();
        $this->assertCount(1, $this->getJson('/api/v1/site-content')->json('data.skills'));
    }

    public function test_patch_section_inconnue_404_et_corps_vide_422(): void
    {
        $this->adminPatch('/api/v1/admin/site-content/inconnue', ['a' => 'b'])
            ->assertNotFound()
            ->assertJsonPath('message', 'Ressource introuvable.');

        $this->adminPatch('/api/v1/admin/site-content/hero', [])
            ->assertStatus(422);
    }

    /* ------------------------------------------------------------- */
    /* Social links / parcours                                        */
    /* ------------------------------------------------------------- */

    public function test_put_social_links_remplace_tout_le_tableau(): void
    {
        $payload = ['links' => [
            ['label' => 'GitHub', 'url' => 'https://github.com/hermann-charo', 'iconName' => 'Github'],
            ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/', 'iconName' => 'Linkedin'],
        ]];

        $response = $this->adminPut('/api/v1/admin/social-links', $payload);
        $response->assertOk()->assertJsonCount(2, 'data');

        // Remplacement complet : Twitter et Mail ont disparu
        $this->assertSame(2, SocialLink::query()->count());
        $this->assertDatabaseMissing('social_links', ['label' => 'Twitter']);
        $public = $this->getJson('/api/v1/social-links')->json('data');
        $this->assertSame('Linkedin', $public[1]['iconName']);
    }

    public function test_put_experience_et_education_camelcase(): void
    {
        $experienceId = Experience::query()->where('company', 'Freelance')->value('id');

        $this->adminPut("/api/v1/admin/experiences/{$experienceId}", [
            'company' => 'Freelance Pro',
            'position' => 'Développeur Fullstack Senior',
            'description' => 'Description mise à jour pour le test.',
            'location' => 'Remote',
            'startDate' => '2023-06-01',
            'endDate' => '2023-12-01',
            'sortOrder' => 5,
        ])->assertOk()
          ->assertJsonPath('data.company', 'Freelance Pro')
          ->assertJsonPath('data.sortOrder', 5);

        $educationId = DB::table('education')->where('school', 'Université de Douala')->value('id');
        $this->adminPut("/api/v1/admin/education/{$educationId}", [
            'school' => 'Université de Douala — Master',
            'degree' => 'Master en Génie Logiciel',
            'description' => 'Cycle master mis à jour.',
            'startDate' => '2022-09-01',
            'endDate' => null,
            'sortOrder' => 1,
        ])->assertOk()
          ->assertJsonPath('data.degree', 'Master en Génie Logiciel')
          ->assertJsonPath('data.endDate', null);
    }
}
