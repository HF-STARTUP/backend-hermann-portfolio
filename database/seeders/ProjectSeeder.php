<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Les 6 projets p1–p6 du mock, textes et slugs identiques.
     * Dates mock « YYYY-MM » converties en Y-m-d (1er jour du mois).
     */
    public function run(): void
    {
        $category = fn (string $label) => ProjectCategory::query()->where('label', $label)->value('id');

        $projects = [
            [
                'slug' => 'vtc-control',
                'title' => 'VTC Control',
                'role' => 'Développeur Frontend Lead',
                'description' => 'Plateforme de gestion de flotte VTC : abonnements, paiements Mobile Money, tracking GPS temps réel et tableaux de bord analytiques.',
                'long_description' => "VTC Control est une plateforme SaaS complète de gestion pour sociétés de transport : gestion des chauffeurs et véhicules, abonnements et facturation, intégration des agrégateurs de paiement (MTN MoMo, Orange Money), suivi GPS temps réel sur carte, messagerie intégrée et reporting avancé. J'ai conçu l'intégralité du frontend avec Next.js, un design system interne sur Tailwind CSS et une couche temps réel via Socket.IO.",
                'image_url' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&q=80',
                'tech_stack' => ['Next.js', 'TypeScript', 'Tailwind CSS', 'Socket.IO', 'TanStack Query'],
                'project_category_id' => $category('Fullstack'),
                'demo_url' => 'https://vtccontrol.com',
                'github_url' => 'https://github.com/hermann-charo',
                'featured' => true,
                'start_date' => '2024-01-01',
                'end_date' => null,
            ],
            [
                'slug' => 'y-pay-wallet',
                'title' => 'Y-PAY Wallet',
                'role' => 'Développeur Frontend & Intégrations Paiement',
                'description' => 'Application de portefeuille mobile money : transferts, paiement de factures et historique de transactions avec une UX fluide.',
                'long_description' => "Y-PAY est une application fintech permettant les transferts d'argent mobile, le paiement de factures et la gestion de budget. J'ai travaillé sur l'interface web d'administration, les parcours d'onboarding et l'intégration des API des opérateurs télécoms (MTN, Orange) côté frontend.",
                'image_url' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=1200&q=80',
                'tech_stack' => ['React', 'Node.js', 'REST API', 'MTN MoMo', 'Orange Money'],
                'project_category_id' => $category('Web'),
                'demo_url' => 'https://play.google.com',
                'github_url' => 'https://github.com/hermann-charo',
                'featured' => true,
                'start_date' => '2023-09-01',
                'end_date' => '2024-05-01',
            ],
            [
                'slug' => 'whatsapp-business',
                'title' => 'WhatsApp Business Hub',
                'role' => 'Développeur Frontend',
                'description' => 'Interface de gestion de conversations WhatsApp Business : messagerie temps réel, contacts, réponses automatiques et analytics.',
                'long_description' => 'Hub centralisé pour gérer plusieurs numéros WhatsApp Business : boîte de réception unifiée avec messagerie temps réel (WebSocket), gestion des contacts et labels, modèles de messages, réponses automatiques et statistiques d\'engagement. Interface construite avec React, Zustand et socket.io-client.',
                'image_url' => 'https://images.unsplash.com/photo-1611606063065-ee7946f0787a?w=1200&q=80',
                'tech_stack' => ['React', 'Zustand', 'socket.io-client', 'Tailwind CSS'],
                'project_category_id' => $category('Web'),
                'demo_url' => null,
                'github_url' => 'https://github.com/hermann-charo',
                'featured' => true,
                'start_date' => '2025-06-01',
                'end_date' => null,
            ],
            [
                'slug' => 'agora-labs',
                'title' => 'AgoraLabs CRM',
                'role' => 'Développeur Frontend',
                'description' => "CRM d'optimisation de conversion : pipelines commerciaux, campagnes et tableaux de bord orientés data.",
                'long_description' => null,
                'image_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&q=80',
                'tech_stack' => ['Vue.js', 'Nuxt.js', 'Pinia', 'Chart.js'],
                'project_category_id' => $category('Web'),
                'demo_url' => 'https://agoralabs.org',
                'github_url' => null,
                'featured' => false,
                'start_date' => '2023-03-01',
                'end_date' => '2023-08-01',
            ],
            [
                'slug' => 'fleet-mobile',
                'title' => 'Fleet Manager Mobile',
                'role' => 'Développeur Flutter',
                'description' => 'Application mobile cross-platform pour chauffeurs : courses, feuilles de route et notifications push.',
                'long_description' => null,
                'image_url' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=1200&q=80',
                'tech_stack' => ['Flutter', 'Dart', 'Firebase', 'REST API'],
                'project_category_id' => $category('Mobile'),
                'demo_url' => null,
                'github_url' => null,
                'featured' => false,
                'start_date' => '2024-06-01',
                'end_date' => '2024-11-01',
            ],
            [
                'slug' => 'design-system-vtc',
                'title' => 'Design System interne',
                'role' => 'UI Engineer',
                'description' => 'Design system réutilisable (composants, tokens, documentation) consommé par les applications VTC Control.',
                'long_description' => null,
                'image_url' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=1200&q=80',
                'tech_stack' => ['Storybook', 'Tailwind CSS', 'Radix UI', 'TypeScript'],
                'project_category_id' => $category('Design'),
                'demo_url' => null,
                'github_url' => null,
                'featured' => false,
                'start_date' => '2024-08-01',
                'end_date' => '2025-01-01',
            ],
        ];

        foreach ($projects as $project) {
            Project::query()->create($project);
        }
    }
}
