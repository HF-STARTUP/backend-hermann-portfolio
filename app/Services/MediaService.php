<?php

namespace App\Services;

use App\Exceptions\UploadedFileTooLarge;
use App\Exceptions\UnsupportedMediaType;
use App\Models\MediaItem;
use enshrined\svgSanitize\Sanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Unique point de contact avec le disque pour la médiathèque.
 */
class MediaService
{
    /** Taille maximale d'upload en octets (alignée sur MAX_UPLOAD_BYTES du frontend). */
    public const MAX_BYTES = 3 * 1024 * 1024;

    /** Types MIME autorisés. */
    public const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/svg+xml',
    ];

    /**
     * Stocke le fichier (nommage hashé, disque public) et crée la ligne media_items.
     *
     * @throws UploadedFileTooLarge
     * @throws UnsupportedMediaType
     */
    public function store(UploadedFile $file): MediaItem
    {
        if ($file->getSize() > self::MAX_BYTES) {
            throw new UploadedFileTooLarge();
        }

        // Vérification du contenu réel, jamais de la seule extension
        $mime = $file->getMimeType() ?: '';

        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new UnsupportedMediaType();
        }

        // Double contrôle du contenu pour les images raster
        if ($mime !== 'image/svg+xml') {
            $info = @getimagesize($file->getRealPath());

            if ($info === false || ! str_starts_with((string) $info['mime'], 'image/')) {
                throw new UnsupportedMediaType();
            }
        }

        $extension = $this->canonicalExtension($mime, $file->getClientOriginalExtension());
        $path = 'media/'.Str::ulid()->toBase32().'.'.$extension;

        $content = $file->getContent();

        if ($mime === 'image/svg+xml') {
            $content = $this->sanitizeSvg($content);
        }

        Storage::disk('public')->put($path, $content);

        return MediaItem::create([
            'name' => $file->getClientOriginalName(),
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $mime,
            'size' => strlen($content),
        ]);
    }

    /**
     * Supprime le fichier physique puis la ligne en base.
     */
    public function delete(MediaItem $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }

    /**
     * URL absolue d'un chemin média. Null-safe ; les URL http(s)
     * déjà absolues (médias distants des seeds) sont retournées telles quelles.
     */
    public static function absoluteUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (preg_match('#^https?://#', $path) === 1) {
            return $path;
        }

        return rtrim(config('app.url'), '/').Storage::url($path);
    }

    /**
     * Assainit un SVG : suppression <script>, handlers on*, foreignObject.
     */
    private function sanitizeSvg(string $svg): string
    {
        $sanitizer = new Sanitizer();
        $clean = $sanitizer->sanitize($svg);

        return $clean ?? throw new UnsupportedMediaType();
    }

    /**
     * Extension canonique à partir du MIME réel détecté.
     */
    private function canonicalExtension(string $mime, string $original): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            default => strtolower($original ?: 'bin'),
        };
    }
}
