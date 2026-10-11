<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    /**
     * Les 4 articles b1–b4 du mock, blocs de contenu complets
     * (paragraph / heading / quote / code). b3 couverture youtube,
     * b4 couverture vidéo mp4.
     */
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'gerer-etat-complexe-nextjs',
                'title' => 'Gérer un état complexe dans Next.js : Zustand vs Context vs TanStack Query',
                'excerpt' => "Retour d'expérience sur le choix des outils de state management dans une application Next.js de taille moyenne : ce que chaque solution apporte, et comment les combiner sans se tirer une balle dans le pied.",
                'cover_image_url' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=1200&q=80',
                'cover_type' => 'image',
                'cover_video_url' => null,
                'cover_youtube_url' => null,
                'tags' => ['Next.js', 'State Management', 'Zustand'],
                'published_at' => '2025-07-12',
                'read_time_minutes' => 8,
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Quand une application Next.js grandit, la question du state management devient centrale. Entre le state local, le Context API, les librairies dédiées comme Zustand et la gestion des données serveur avec TanStack Query, il est facile de se perdre.',
                    ],
                    ['type' => 'heading', 'text' => 'Le bon outil pour la bonne donnée'],
                    [
                        'type' => 'paragraph',
                        'text' => "Ma règle simple : les données venant du serveur appartiennent à TanStack Query (cache, invalidation, revalidation), l'état UI global léger à Zustand, et tout ce qui reste local à un composant ne devrait jamais quitter ce composant.",
                    ],
                    [
                        'type' => 'quote',
                        'text' => 'La meilleure architecture de state est celle que ton équipe comprend en relisant le code trois mois plus tard.',
                        'author' => 'Principe personnel',
                    ],
                    ['type' => 'heading', 'text' => 'Exemple de store Zustand'],
                    [
                        'type' => 'code',
                        'language' => 'typescript',
                        'text' => "import { create } from 'zustand';\n\ninterface CartStore {\n  items: string[];\n  addItem: (id: string) => void;\n}\n\nexport const useCartStore = create<CartStore>((set) => ({\n  items: [],\n  addItem: (id) =>\n    set((state) => ({ items: [...state.items, id] })),\n}));",
                    ],
                    [
                        'type' => 'paragraph',
                        'text' => "Cette séparation nette a permis à mon équipe de réduire de moitié les bugs liés à la synchronisation d'état sur VTC Control.",
                    ],
                ],
            ],
            [
                'slug' => 'tailwind-css-design-system',
                'title' => 'Construire un design system avec Tailwind CSS et Radix UI',
                'excerpt' => 'Comment structurer ses tokens, composer des composants accessibles avec Radix et garder une base de code Tailwind maintenable sur le long terme.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1507721999472-8ed4421c4af2?w=1200&q=80',
                'cover_type' => 'image',
                'cover_video_url' => null,
                'cover_youtube_url' => null,
                'tags' => ['Tailwind CSS', 'Design System', 'Accessibilité'],
                'published_at' => '2025-05-28',
                'read_time_minutes' => 6,
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => "Un design system n'est pas juste une collection de composants : c'est un langage commun entre design et développement. Voici comment je structure mes tokens de couleur, d'espacement et de typographie.",
                    ],
                    ['type' => 'heading', 'text' => 'Des variables CSS aux classes utilitaires'],
                    [
                        'type' => 'code',
                        'language' => 'css',
                        'text' => ":root {\n  --background: 228 40% 97%;\n  --primary: 258 89% 62%;\n}\n\n.dark {\n  --background: 240 52% 4%;\n}",
                    ],
                    [
                        'type' => 'paragraph',
                        'text' => 'En mappant ces variables dans tailwind.config.ts, chaque composant devient théme-aware sans effort : bg-background, text-foreground fonctionnent dans les deux modes.',
                    ],
                ],
            ],
            [
                'slug' => 'temps-reel-websockets-react',
                'title' => 'Temps réel dans React : bien utiliser les WebSockets avec Socket.IO',
                'excerpt' => "Patterns que j'utilise pour une connexion WebSocket robuste : reconnexion, rooms, optimistic updates et gestion mémoire des listeners.",
                'cover_image_url' => 'https://images.unsplash.com/photo-1551703599-2a4e224a1b16?w=1200&q=80',
                'cover_type' => 'youtube',
                'cover_video_url' => null,
                'cover_youtube_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
                'tags' => ['React', 'WebSocket', 'Socket.IO'],
                'published_at' => '2025-04-15',
                'read_time_minutes' => 7,
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Sur un chat temps réel ou du suivi GPS, la qualité perçue dépend surtout de la robustesse de la couche réseau. Une mauvaise gestion de reconnexion transforme une démo impressionnante en produit frustrant.',
                    ],
                    ['type' => 'heading', 'text' => 'Toujours nettoyer ses listeners'],
                    [
                        'type' => 'code',
                        'language' => 'typescript',
                        'text' => "useEffect(() => {\n  socket.on('message:new', onNewMessage);\n  return () => {\n    socket.off('message:new', onNewMessage);\n  };\n}, [onNewMessage]);",
                    ],
                    [
                        'type' => 'paragraph',
                        'text' => 'Ce simple cleanup évite les duplications de messages après navigation — le bug temps réel le plus courant que je rencontre en code review.',
                    ],
                ],
            ],
            [
                'slug' => 'flutter-pour-developpeur-web',
                'title' => 'Flutter vu par un développeur web : convergences et pièges',
                'excerpt' => 'Venir de React vers Flutter : ce qui se transfère naturellement (composition, state) et ce qui surprend (layout, asynchronisme, outillage).',
                'cover_image_url' => 'https://images.unsplash.com/photo-1526498843955-2e45a3be98fe?w=1200&q=80',
                'cover_type' => 'video',
                'cover_video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
                'cover_youtube_url' => null,
                'tags' => ['Flutter', 'Mobile', 'Cross-platform'],
                'published_at' => '2025-02-20',
                'read_time_minutes' => 5,
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => "Après plusieurs années sur React, apprendre Flutter m'a surpris par sa familiarité : composition de widgets, state management par stores immuables, hot reload… Mais certaines habitudes web jouent des tours.",
                    ],
                    ['type' => 'heading', 'text' => 'Le piège du layout'],
                    [
                        'type' => 'paragraph',
                        'text' => "Contrairement au CSS, Flutter ne fait pas d'hypothèses implicites : tout est contraintes explicites. Comprendre RenderBox et le pipeline de layout est le vrai déclic.",
                    ],
                ],
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::query()->create($post);
        }
    }
}
