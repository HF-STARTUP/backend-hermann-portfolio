<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class ApiTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // État initial = seed du mock (portfolio.mock.ts)
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        // Le cache « array » est partagé au niveau processus : purge les
        // compteurs de rate limiting hérités des tests précédents.
        \Illuminate\Support\Facades\Cache::flush();
    }

    /** Login admin et retourne le token Bearer. */
    protected function login(): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => env('ADMIN_EMAIL'),
            'password' => env('ADMIN_PASSWORD'),
        ]);

        $this->assertTrue($response->status() === 200, 'Login de test échoué : '.$response->content());

        return (string) $response->json('data.token');
    }

    /** GET authentifié avec le token admin. */
    protected function adminGet(string $uri, array $headers = []): TestResponse
    {
        return $this->withToken($this->login())->getJson($uri, $headers);
    }

    protected function adminPost(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->withToken($this->login())->postJson($uri, $data, $headers);
    }

    protected function adminPatch(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->withToken($this->login())->patchJson($uri, $data, $headers);
    }

    protected function adminPut(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->withToken($this->login())->putJson($uri, $data, $headers);
    }

    protected function adminDelete(string $uri, array $headers = []): TestResponse
    {
        return $this->withToken($this->login())->deleteJson($uri, $headers);
    }
}
