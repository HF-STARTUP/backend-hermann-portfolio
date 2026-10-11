<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\DB;

/**
 * POST /api/v1/contact — 201, anti-spam 5/min/IP, messages de validation FR.
 */
class ContactTest extends ApiTestCase
{
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Sujet de test',
            'message' => 'Un message de test suffisamment long pour passer la validation.',
        ], $overrides);
    }

    public function test_contact_cree_le_message_avec_read_at_null_et_reponse_exacte(): void
    {
        $response = $this->postJson('/api/v1/contact', $this->payload());

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message',
                'Votre message a bien été envoyé. Je vous répondrai dans les plus brefs délais.');

        $this->assertDatabaseCount('contact_messages', 4); // 3 démo + celui-ci
        $row = ContactMessage::query()->where('email', 'test@example.com')->first();
        $this->assertNotNull($row);
        $this->assertNull($row->read_at);
    }

    public function test_validation_nom_manquant_message_exact(): void
    {
        $this->postJson('/api/v1/contact', $this->payload(['name' => '']))
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Échec de la validation.')
            ->assertJsonPath('errors.name.0', 'Votre nom est requis');
    }

    public function test_validation_sujet_manquant(): void
    {
        $this->postJson('/api/v1/contact', $this->payload(['subject' => null]))
            ->assertStatus(422)
            ->assertJsonPath('errors.subject.0', 'Le sujet est requis');
    }

    public function test_validation_message_trop_court(): void
    {
        $this->postJson('/api/v1/contact', $this->payload(['message' => 'court']))
            ->assertStatus(422)
            ->assertJsonPath('errors.message.0', 'Votre message doit contenir au moins 10 caractères');
    }

    public function test_sixieme_appel_est_limite_a_429(): void
    {
        foreach (range(1, 5) as $i) {
            $this->postJson('/api/v1/contact', $this->payload(['email' => "u{$i}@example.com"]))
                ->assertStatus(201);
        }

        $sixth = $this->postJson('/api/v1/contact', $this->payload(['email' => 'u6@example.com']));

        $sixth->assertStatus(429)->assertJsonPath('success', false);
        $this->assertSame('Trop de tentatives. Réessayez plus tard.', $sixth->json('message'));
        // Les 5 premiers messages légitimes sont enregistrés (3 démo + 5), pas le 6e
        $this->assertSame(8, DB::table('contact_messages')->count());
    }
}
