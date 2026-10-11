<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * POST /api/v1/auth/login · logout · me — Sanctum, ability admin,
 * anti-énumération (email inconnu == mauvais mot de passe).
 */
class AuthTest extends ApiTestCase
{
    public function test_login_renvoie_un_token_utilisable(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => env('ADMIN_EMAIL'),
            'password' => env('ADMIN_PASSWORD'),
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', env('ADMIN_EMAIL'));

        $token = (string) $response->json('data.token');
        $this->assertNotSame('', $token);
        // expiresAt ISO-8601
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T/', (string) $response->json('data.expiresAt'));

        // Le token donne accès à /me
        $me = $this->withToken($token)->getJson('/api/v1/auth/me');
        $me->assertOk()->assertJsonPath('data.email', env('ADMIN_EMAIL'));

        // ... et aux routes admin
        $this->withToken($token)->getJson('/api/v1/admin/messages')->assertOk();
    }

    public function test_mauvais_mot_de_passe_et_email_inconnu_sont_indiscernables(): void
    {
        $wrongPassword = $this->postJson('/api/v1/auth/login', [
            'email' => env('ADMIN_EMAIL'),
            'password' => 'mauvais-mot-de-passe',
        ]);
        $unknownEmail = $this->postJson('/api/v1/auth/login', [
            'email' => 'inconnu@example.com',
            'password' => 'peu-importe-123',
        ]);

        foreach ([$wrongPassword, $unknownEmail] as $response) {
            $response->assertStatus(401)
                ->assertJsonPath('success', false)
                ->assertExactJson([
                    'success' => false,
                    'message' => 'Identifiants incorrects.',
                    'data' => null,
                ]);
        }

        // Corps strictement identique (anti-énumération)
        $this->assertSame(
            $wrongPassword->content(),
            $unknownEmail->content()
        );
    }

    public function test_routes_admin_inaccessibles_sans_token(): void
    {
        $this->getJson('/api/v1/admin/projects')
            ->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Non authentifié.');
    }

    public function test_token_sans_ability_admin_est_refuse_403(): void
    {
        /** @var User $user */
        $user = User::query()->where('email', env('ADMIN_EMAIL'))->firstOrFail();

        // Token valide mais ability « viewer » ≠ admin
        $token = $user->createToken('viewer', ['viewer'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/admin/projects')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Accès refusé.');
    }

    public function test_logout_revoque_le_token_courant(): void
    {
        $token = $this->login();

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();

        // Artefact de tests fonctionnels : RequestGuard met l'utilisateur en
        // cache pour tout le process applicatif partagé par les requêtes du
        // test. Chaque vraie requête repart d'une instance vierge.
        $this->app['auth']->forgetGuards();

        // Le token révoqué ne fonctionne plus
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertStatus(401);
    }
}
