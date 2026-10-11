<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ProjectCategory;
use App\Models\TechCategory;

/**
 * Endpoints publics — enveloppe {success, message, data} et filtres.
 */
class PublicEndpointsTest extends ApiTestCase
{
    public function test_site_content_renvoie_les_6_sections_avec_le_seed_du_mock(): void
    {
        $response = $this->getJson('/api/v1/site-content');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.profile.fullName', 'Hermann FOMO')
            ->assertJsonPath('data.hero.ctaLabel', 'Voir mes projets')
            ->assertJsonPath('data.footer.copyrightPrefix', 'Hermann FOMO');

        $this->assertCount(9, $response->json('data.skills'));
        $this->assertCount(4, $response->json('data.hero.stats'));

        // homeSections fusionnées à plat (contrat frontend)
        $this->assertSame('À propos', $response->json('data.homeSections.about.badge'));
    }

    public function test_projects_renvoie_les_6_projets_au_format_camelcase(): void
    {
        $response = $this->getJson('/api/v1/projects');

        $response->assertOk()->assertJsonPath('success', true);

        $projects = $response->json('data');
        $this->assertCount(6, $projects);

        $first = collect($projects)->firstWhere('slug', 'vtc-control');
        $this->assertNotNull($first);
        $this->assertSame('VTC Control', $first['title']);
        $this->assertTrue($first['featured']);
        $this->assertSame('2024-01-01', $first['startDate']);
        $this->assertNull($first['endDate']);
        // Spécification §4.3 : liens aplaties demoUrl/githubUrl
        $this->assertSame('https://vtccontrol.com', $first['links']['demoUrl']);
        $this->assertSame('https://github.com/hermann-charo', $first['links']['githubUrl']);
    }

    public function test_filtre_featured_ne_retourne_que_3_projets(): void
    {
        $response = $this->getJson('/api/v1/projects?featured=1');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_filtre_category_par_ulid(): void
    {
        $categoryId = ProjectCategory::query()->where('label', 'Fullstack')->value('id');

        $response = $this->getJson('/api/v1/projects?category='.$categoryId);

        $response->assertOk();
        $projects = $response->json('data');
        $this->assertCount(1, $projects);
        $this->assertSame('vtc-control', $projects[0]['slug']);
    }

    public function test_show_projet_par_slug_et_404_si_inconnu(): void
    {
        $this->getJson('/api/v1/projects/y-pay-wallet')
            ->assertOk()
            ->assertJsonPath('data.slug', 'y-pay-wallet')
            ->assertJsonPath('data.title', 'Y-PAY Wallet');

        $this->getJson('/api/v1/projects/slug-inexistant')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Ressource introuvable.');
    }

    public function test_blog_posts_tries_par_date_desc_et_filtre_tag(): void
    {
        $response = $this->getJson('/api/v1/blog-posts');

        $response->assertOk();
        $posts = $response->json('data');
        $this->assertCount(4, $posts);
        $this->assertSame('2025-07-12', $posts[0]['publishedAt']);
        $this->assertSame('gerer-etat-complexe-nextjs', $posts[0]['slug']);

        // b3 est le seul taggé « React »
        $tagged = $this->getJson('/api/v1/blog-posts?tag=React')->json('data');
        $this->assertCount(1, $tagged);
        $this->assertSame('temps-reel-websockets-react', $tagged[0]['slug']);
    }

    public function test_blog_post_youtube_expose_cover_youtube_url(): void
    {
        $post = $this->getJson('/api/v1/blog-posts/temps-reel-websockets-react');

        $post->assertOk()
            ->assertJsonPath('data.coverType', 'youtube')
            ->assertJsonPath('data.coverYoutubeUrl', 'https://www.youtube.com/watch?v=aqz-KE-bpKQ')
            ->assertJsonMissing(['coverVideoUrl' => 'x']);

        // b4 : couverture vidéo
        $video = $this->getJson('/api/v1/blog-posts/flutter-pour-developpeur-web');
        $video->assertOk()
            ->assertJsonPath('data.coverType', 'video')
            ->assertJsonPath('data.coverVideoUrl',
                'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4');
    }

    public function test_taxonomies_et_technologies(): void
    {
        $this->getJson('/api/v1/project-categories')->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.label', 'Web');

        $this->getJson('/api/v1/tech-categories')->assertOk()
            ->assertJsonCount(6, 'data');

        $all = $this->getJson('/api/v1/technologies')->assertOk();
        $this->assertCount(30, $all->json('data'));

        $frontendId = TechCategory::query()->where('name', 'Frontend')->value('id');
        $frontend = $this->getJson('/api/v1/technologies?categoryId='.$frontendId);
        $this->assertCount(6, $frontend->json('data'));
        $this->assertSame('React', $frontend->json('data.0.name'));
    }

    public function test_parcours_et_liens_sociaux(): void
    {
        $this->getJson('/api/v1/experiences')->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.company', 'Genius Technologies');

        $this->getJson('/api/v1/education')->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.school', 'Université de Douala');

        $links = $this->getJson('/api/v1/social-links')->assertOk();
        $this->assertCount(4, $links->json('data'));
        $this->assertSame('Github', $links->json('data.0.iconName'));
    }
}
