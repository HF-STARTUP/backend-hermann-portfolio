<?php

namespace Database\Seeders;

use App\Models\ProjectCategory;
use App\Models\TechCategory;
use App\Models\Technology;
use Illuminate\Database\Seeder;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $projectCategories = [
            'Web',
            'Mobile',
            'Fullstack',
            'Design',
        ];
        foreach ($projectCategories as $label) {
            ProjectCategory::query()->create(['label' => $label]);
        }

        $techCategories = [
            'Frontend',
            'Backend',
            'Mobile',
            'Bases de données',
            'DevOps & Cloud',
            'Outils',
        ];
        $techCategoryIds = [];
        foreach ($techCategories as $name) {
            $techCategoryIds[$name] = TechCategory::query()->create(['name' => $name])->id;
        }

        /* 31 technologies du mock, image_url null (icônes remplacées par le backend) */
        $technologies = [
            ['Frontend', ['React', 'Next.js', 'TypeScript', 'JavaScript', 'Tailwind CSS', 'Redux']],
            ['Backend', ['Node.js', 'Express', 'NestJS', 'Laravel', 'PHP', 'Python', 'Django']],
            ['Mobile', ['Flutter', 'Firebase']],
            ['Bases de données', ['PostgreSQL', 'MySQL', 'Redis', 'Prisma']],
            ['DevOps & Cloud', ['Docker', 'Linux', 'Nginx', 'Ansible', 'Vercel']],
            ['Outils', ['Git', 'GitHub', 'GitLab', 'Figma', 'Postman', 'Storybook']],
        ];
        foreach ($technologies as [$categoryName, $names]) {
            foreach ($names as $name) {
                Technology::query()->create([
                    'name' => $name,
                    'tech_category_id' => $techCategoryIds[$categoryName],
                    'image_url' => null,
                ]);
            }
        }
    }
}
