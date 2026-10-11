<?php

namespace Tests\Feature;

use App\Models\MediaItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Médiathèque admin : upload réel (MIME vérifié au contenu), 413 / 415,
 * suppression fichier physique.
 */
class MediaUploadTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** PNG 1x1 valide (base64). */
    private function pngBytes(): string
    {
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );
    }

    public function test_upload_png_et_jpg_sont_acceptees_201(): void
    {
        $png = UploadedFile::fake()->createWithContent('a.png', $this->pngBytes());
        $jpg = UploadedFile::fake()->image('c.jpg', width: 10, height: 10);

        $pngResponse = $this->withToken($this->login())
            ->post('/api/v1/admin/media', ['file' => $png]);
        $pngResponse->assertStatus(201);
        $this->assertSame('a.png', $pngResponse->json('data.name'));
        $this->assertStringContainsString('/storage/media/', (string) $pngResponse->json('data.src'));

        $jpgResponse = $this->withToken($this->login())
            ->post('/api/v1/admin/media', ['file' => $jpg]);
        $jpgResponse->assertStatus(201);
    }

    public function test_fichier_de_plus_de_3mo_est_refuse_413(): void
    {
        $big = UploadedFile::fake()->create('huge.jpg', 4000, 'image/jpeg');

        $response = $this->withToken($this->login())
            ->post('/api/v1/admin/media', ['file' => $big]);

        $response->assertStatus(413);
        $this->assertSame(
            'Le fichier dépasse la taille maximale autorisée (3 Mo).',
            $response->json('message')
        );
    }

    public function test_txt_renomme_en_jpg_est_refuse_415(): void
    {
        $fake = UploadedFile::fake()->createWithContent('photo.jpg', 'Ceci est du texte, pas une image.');

        $response = $this->withToken($this->login())
            ->post('/api/v1/admin/media', ['file' => $fake]);

        $response->assertStatus(415);
        $this->assertSame(
            'Type de fichier non autorisé (jpeg, png, webp, gif ou svg attendu).',
            $response->json('message')
        );
    }

    public function test_upload_sans_authentification_est_refuse(): void
    {
        $png = UploadedFile::fake()->createWithContent('x.png', $this->pngBytes());

        $this->post('/api/v1/admin/media', ['file' => $png])->assertStatus(401);
    }

    public function test_delete_supprime_le_fichier_physique_et_la_ligne(): void
    {
        $media = MediaItem::query()->create([
            'name' => 'fichier-test.png',
            'disk' => 'public',
            'path' => 'media/test-delete.png',
            'mime_type' => 'image/png',
            'size' => 70,
        ]);
        Storage::disk('public')->put($media->path, $this->pngBytes());
        $this->assertTrue(Storage::disk('public')->exists($media->path));

        $this->adminDelete('/api/v1/admin/media/'.$media->id)->assertNoContent();

        Storage::disk('public')->assertMissing($media->path);
        $this->assertDatabaseMissing('media_items', ['id' => $media->id]);
    }
}
