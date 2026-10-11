<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\MediaItem;
use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    /**
     * Parcours, liens sociaux, médiathèque (10 visuels Unsplash du mock)
     * et 3 messages de démonstration. Dates « YYYY-MM » → Y-m-d.
     */
    public function run(): void
    {
        $experiences = [
            [
                'company' => 'Genius Technologies',
                'position' => 'Développeur Frontend Lead',
                'description' => "Conception du frontend de VTC Control (SaaS de gestion de flotte) : architecture Next.js, design system interne, temps réel Socket.IO et encadrement de deux développeurs juniors.",
                'location' => 'Douala, Cameroun',
                'start_date' => '2024-01-01',
                'end_date' => null,
                'sort_order' => 1,
            ],
            [
                'company' => 'Freelance',
                'position' => 'Développeur Fullstack',
                'description' => 'Missions variées : applications de gestion, sites vitrines performants, intégrations paiement Mobile Money et déploiement sur VPS Linux.',
                'location' => 'À distance',
                'start_date' => '2023-06-01',
                'end_date' => '2023-12-01',
                'sort_order' => 2,
            ],
            [
                'company' => 'Agence Digitale Douala',
                'position' => 'Développeur Web Junior',
                'description' => "Premières expériences professionnelles : intégration d'interfaces responsive, optimisation SEO technique et maintenance d'applications Laravel.",
                'location' => 'Douala, Cameroun',
                'start_date' => '2022-09-01',
                'end_date' => '2023-05-01',
                'sort_order' => 3,
            ],
        ];
        foreach ($experiences as $experience) {
            Experience::query()->create($experience);
        }

        $education = [
            [
                'school' => 'Université de Douala',
                'degree' => 'Licence en Informatique',
                'description' => "Fondamentaux : algorithmique, réseaux, systèmes d'exploitation, bases de données.",
                'start_date' => '2019-10-01',
                'end_date' => '2022-07-01',
                'sort_order' => 1,
            ],
            [
                'school' => 'Certifications en ligne',
                'degree' => 'AWS / Docker / Administration Linux',
                'description' => 'Autoformation continue via des certifications techniques.',
                'start_date' => '2022-01-01',
                'end_date' => null,
                'sort_order' => 2,
            ],
        ];
        foreach ($education as $item) {
            Education::query()->create($item);
        }

        $socialLinks = [
            ['label' => 'GitHub', 'url' => 'https://github.com/hermann-charo', 'icon_name' => 'Github'],
            ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/', 'icon_name' => 'Linkedin'],
            ['label' => 'Twitter', 'url' => 'https://twitter.com/', 'icon_name' => 'Twitter'],
            ['label' => 'Email', 'url' => 'mailto:contact@hermannfomo.dev', 'icon_name' => 'Mail'],
        ];
        foreach ($socialLinks as $index => $link) {
            SocialLink::query()->create($link + ['sort_order' => $index + 1]);
        }

        /* Médiathèque = les 10 images déjà utilisées par le mock.
           URL externes stockées telles quelles (pass-through absoluteUrl). */
        $media = [
            ['Projet VTC Control', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&q=80'],
            ['Projet Y-PAY Wallet', 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=1200&q=80'],
            ['Projet WhatsApp Business', 'https://images.unsplash.com/photo-1611606063065-ee7946f0787a?w=1200&q=80'],
            ['Projet AgoraLabs', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&q=80'],
            ['Projet Fleet Manager', 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=1200&q=80'],
            ['Projet Design System', 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=1200&q=80'],
            ['Article état complexe', 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=1200&q=80'],
            ['Article design system', 'https://images.unsplash.com/photo-1507721999472-8ed4421c4af2?w=1200&q=80'],
            ['Article temps réel', 'https://images.unsplash.com/photo-1551703599-2a4e224a1b16?w=1200&q=80'],
            ['Article Flutter', 'https://images.unsplash.com/photo-1526498843955-2e45a3be98fe?w=1200&q=80'],
        ];
        foreach ($media as [$name, $url]) {
            MediaItem::query()->create([
                'name' => $name,
                'disk' => 'public',
                'path' => $url,
                'mime_type' => 'image/jpeg',
                'size' => 0,
            ]);
        }

        /* Messages démo m1–m3 — sentAt reproduit via created_at. */
        $messages = [
            [
                'name' => 'Aïcha Mbarga',
                'email' => 'aicha.mbarga@exemple.cm',
                'subject' => "Refonte d'un site vitrine",
                'message' => 'Bonjour Hermann, je souhaite faire évoluer le site de mon entreprise avec une refonte complète. Auriez-vous disponibilité pour en discuter la semaine prochaine ?',
                'sentAt' => '2025-08-14T10:24:00.000Z',
                'read' => false,
            ],
            [
                'name' => 'Jean-Luc Kamdem',
                'email' => 'jl.kamdem@exemple.com',
                'subject' => 'Opportunité freelance — dashboard React',
                'message' => 'Nous cherchons un développeur frontend expérimenté pour un dashboard analytique de 3 mois. Votre profil sur VTC Control correspond exactement à ce que nous recherchons.',
                'sentAt' => '2025-08-18T16:05:00.000Z',
                'read' => false,
            ],
            [
                'name' => 'Sophie Ngo',
                'email' => 'sophie.ngo@exemple.fr',
                'subject' => 'Question sur votre article Zustand',
                'message' => "Merci pour votre article sur la gestion d'état ! Une question : comment gérez-vous la persistance du store entre les rechargements de page ?",
                'sentAt' => '2025-08-20T09:41:00.000Z',
                'read' => true,
            ],
        ];
        foreach ($messages as $demo) {
            $message = ContactMessage::query()->create([
                'name' => $demo['name'],
                'email' => $demo['email'],
                'subject' => $demo['subject'],
                'message' => $demo['message'],
                'read_at' => $demo['read'] ? now() : null,
            ]);
            $sentAt = \Illuminate\Support\Carbon::parse($demo['sentAt']);
            $message->forceFill(['created_at' => $sentAt, 'updated_at' => $sentAt])->save();
        }
    }
}
