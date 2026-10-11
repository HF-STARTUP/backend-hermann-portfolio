<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    /**
     * Les 6 sections éditables du mock (profile, hero, skills,
     * homeSections, pages, footer) — textes exacts.
     */
    public function run(): void
    {
        $sections = [
            'profile' => [
                'fullName' => 'Hermann FOMO',
                'firstName' => 'Hermann',
                'lastName' => 'FOMO',
                'logoInitials' => 'HF',
                'roleLine' => 'Développeur Frontend',
                'techLine' => 'React.js, Nuxt.js, Next.js, Vue.js, Tailwind.css',
                'location' => 'Résident à Douala, Cameroun',
                'email' => 'contact@hermannfomo.dev',
                'cvUrl' => '',
            ],
            'hero' => [
                'availabilityBadge' => 'Ouvert pour de nouvelles opportunités',
                'description' => "Développeur Frontend avec 2 ans d'expérience, spécialisé en JavaScript et Flutter. Je porte une attention particulière à la qualité et à la fiabilité des interfaces que je construis, appuyée par une bonne maîtrise des environnements sur lesquels elles s'exécutent (Docker, CI/CD, réseaux). Curieux et déterminé, je reste toujours ouvert à la critique constructive pour continuer à progresser.",
                'ctaLabel' => 'Voir mes projets',
                'scrollHint' => 'Découvrir',
                'stats' => [
                    ['id' => 'stat-1', 'value' => '2+', 'label' => "ans d'expérience"],
                    ['id' => 'stat-2', 'value' => '5+', 'label' => 'Projets livrés'],
                    ['id' => 'stat-3', 'value' => '4+', 'label' => 'Framework'],
                    ['id' => 'stat-4', 'value' => '∞', 'label' => 'Adaptation & passion'],
                ],
            ],
            'skills' => [
                [
                    'id' => 'skill-1',
                    'name' => 'Frontend Development',
                    'iconName' => 'MonitorSmartphone',
                    'features' => [
                        'Next.js',
                        'React',
                        'TypeScript',
                        'JavaScript (ES6+)',
                        'Tailwind CSS',
                        'Responsive Design',
                        'REST API Integration',
                    ],
                ],
                [
                    'id' => 'skill-2',
                    'name' => 'Backend Development',
                    'iconName' => 'Code2',
                    'features' => [
                        'Laravel',
                        'PHP',
                        'Django',
                        'Node.js',
                        'REST API Development',
                        'Authentication & Authorization',
                    ],
                ],
                [
                    'id' => 'skill-3',
                    'name' => 'Mobile Development',
                    'iconName' => 'Smartphone',
                    'features' => [
                        'Flutter',
                        'Android Deployment',
                        'Cross-platform Applications',
                        'API Integration',
                        'State Management',
                    ],
                ],
                [
                    'id' => 'skill-4',
                    'name' => 'Database',
                    'iconName' => 'Database',
                    'features' => [
                        'MySQL',
                        'PostgreSQL',
                        'Database Design',
                        'Query Optimization',
                        'ORM (Eloquent)',
                    ],
                ],
                [
                    'id' => 'skill-5',
                    'name' => 'DevOps & Cloud',
                    'iconName' => 'CloudCog',
                    'features' => [
                        'Docker',
                        'CI/CD',
                        'Linux Server Deployment',
                        'Apache Reverse Proxy',
                        'Ansible',
                    ],
                ],
                [
                    'id' => 'skill-6',
                    'name' => 'System Administration',
                    'iconName' => 'ServerCog',
                    'features' => [
                        'Linux Administration',
                        'Windows Server',
                        'DNS',
                        'DHCP',
                        'Zabbix Monitoring',
                    ],
                ],
                [
                    'id' => 'skill-7',
                    'name' => 'Networking',
                    'iconName' => 'Network',
                    'features' => [
                        'Network Configuration',
                        'LAN Infrastructure',
                        'Routing',
                        'Switching',
                        'Virtualization (VMware, VirtualBox)',
                    ],
                ],
                [
                    'id' => 'skill-8',
                    'name' => 'Version Control',
                    'iconName' => 'GitBranch',
                    'features' => ['Git', 'GitHub', 'GitLab', 'Code Review'],
                ],
                [
                    'id' => 'skill-9',
                    'name' => 'Software Engineering',
                    'iconName' => 'ShieldCheck',
                    'features' => [
                        'Problem Solving',
                        'System Architecture',
                        'Performance Optimization',
                        'Agile Development',
                    ],
                ],
            ],
            'homeSections' => [
                'about' => [
                    'badge' => 'À propos',
                    'titlePrefix' => 'Plus de',
                    'titleHighlight' => '2 ans',
                    'titleSuffix' => "d'expertise",
                    'subtitle' => "Développeur Full Stack passionné par la création d'applications performantes et modernes. J'allie une expertise en développement Frontend, Backend et Mobile avec de solides compétences en administration systèmes, DevOps et infrastructures afin de concevoir des solutions robustes, évolutives et centrées sur l'expérience utilisateur.",
                    'ctaLabel' => 'En savoir plus',
                ],
                'technologies' => [
                    'badge' => 'Stack technique',
                    'titlePrefix' => 'Technologies',
                    'titleHighlight' => 'utilisées',
                    'subtitle' => 'Les outils et frameworks que j\'utilise au quotidien pour concevoir des interfaces performantes et maintenables.',
                ],
                'projects' => [
                    'badge' => 'Portfolio',
                    'titlePrefix' => 'Ce que j\'ai',
                    'titleHighlight' => 'construit',
                    'subtitle' => 'Une sélection de mes trois projets principaux — des solutions concrètes, du paiement mobile au temps réel.',
                    'ctaLabel' => 'Voir tous les projets',
                ],
                'contact' => [
                    'badge' => 'Contact',
                    'titlePrefix' => 'Prêt à',
                    'titleHighlight' => 'collaborer ?',
                    'subtitle' => 'Un projet, une opportunité ou juste une question ? Écrivez-moi, je réponds rapidement.',
                    'sideTitle' => 'Restons en contact',
                    'sideDescription' => 'Basé à Douala au Cameroun et disponible à distance pour des missions partout dans le monde.',
                ],
            ],
            'pages' => [
                'about' => [
                    'badge' => 'À propos de moi',
                    'titlePrefix' => 'Derrière le',
                    'titleHighlight' => 'code',
                    'description' => "Développeur passionné par la création d'interfaces performantes, accessibles et centrées sur l'expérience utilisateur.",
                    'bioTitle' => 'Hermann FOMO — Développeur Frontend',
                    'bioParagraphs' => [
                        "Développeur Frontend avec plus de 2 ans d'expérience, spécialisé en JavaScript et Flutter. Je porte une attention particulière à la qualité et à la fiabilité des interfaces que je construis, appuyée par une bonne maîtrise des environnements sur lesquels elles s'exécutent (Docker, CI/CD, réseaux).",
                        "Curieux et déterminé, j'aime concevoir des solutions complètes : du pixel au serveur. Mon objectif est simple — des produits rapides, fiables et agréables à utiliser.",
                    ],
                    'cvCtaLabel' => 'Télécharger mon CV',
                    'contactCtaLabel' => 'Me contacter',
                    'experienceTitle' => 'Expériences professionnelles',
                    'educationTitle' => 'Formation',
                    'skillsTitlePrefix' => 'Ce que je fais',
                    'skillsTitleHighlight' => 'concrètement',
                ],
                'projects' => [
                    'badge' => 'Portfolio',
                    'titlePrefix' => 'Mes',
                    'titleHighlight' => 'projets',
                    'description' => 'Des applications web et mobile concrètes : gestion de flotte, fintech, messagerie temps réel et design systems.',
                ],
                'blog' => [
                    'badge' => 'Blog',
                    'titlePrefix' => 'Articles &',
                    'titleHighlight' => "retours d'expérience",
                    'description' => "Ce que j'apprends au fil de mes projets : patterns React, outillage frontend, mobile et DevOps.",
                ],
            ],
            'footer' => [
                'baseline' => 'Développeur Frontend basé à Douala, Cameroun — disponible pour de nouvelles opportunités.',
                'copyrightPrefix' => 'Hermann FOMO',
                'copyrightSuffix' => '— Construit avec Next.js, Tailwind CSS & framer-motion.',
            ],
        ];

        foreach ($sections as $section => $data) {
            SiteContent::query()->create(['section' => $section, 'data' => $data]);
        }
    }
}
