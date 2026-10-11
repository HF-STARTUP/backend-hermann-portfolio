<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Reproduit exactement l'état du mock frontend (portfolio.mock.ts).
     * Ordre inverse des dépendances pour la troncature.
     */
    public function run(): void
    {
        // Troncature sans se soucier des clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'contact_messages',
            'media_items',
            'social_links',
            'education',
            'experiences',
            'site_content',
            'blog_posts',
            'projects',
            'technologies',
            'tech_categories',
            'project_categories',
            'personal_access_tokens',
            'users',
        ] as $table) {
            DB::table($table)->delete();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $this->call([
            AdminUserSeeder::class,
            TaxonomySeeder::class,
            ProjectSeeder::class,
            BlogPostSeeder::class,
            SiteContentSeeder::class,
            PortfolioSeeder::class,
        ]);
    }
}
