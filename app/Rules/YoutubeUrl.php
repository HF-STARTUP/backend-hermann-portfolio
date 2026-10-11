<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Miroir de getYoutubeVideoId du frontend (src/utils/media.ts) :
 * accepte youtube.com/watch?v=ID, youtu.be/ID, /shorts/ID, /embed/ID,
 * /live/ID ou un ID brut. Le contrôleur stocke l'URL brute reçue.
 */
class YoutubeUrl implements ValidationRule
{
    /**
     * Extrait l'identifiant YouTube d'une URL (ou retourne null).
     */
    public static function extractId(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        // Identifiant brut déjà fourni
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        foreach ([
            '/(?:youtube\.com\/watch\?(?:.*&)?v=)([a-zA-Z0-9_-]{11})/',
            '/(?:youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            '/(?:youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/',
            '/(?:youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
            '/(?:youtube\.com\/live\/)([a-zA-Z0-9_-]{11})/',
        ] as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || self::extractId($value) === null) {
            $fail('Lien YouTube invalide (ex: https://www.youtube.com/watch?v=...)');
        }
    }
}
