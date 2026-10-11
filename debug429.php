<?php
use Illuminate\Support\Facades\RateLimiter;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// Simule 6 requêtes contact depuis la même IP via le kernel HTTP
for ($i = 1; $i <= 6; $i++) {
    $request = Illuminate\Http\Request::create('/api/v1/contact', 'POST', [
        'name' => 'U'.$i,
        'email' => "u{$i}@example.com",
        'subject' => 'Sujet '.$i,
        'message' => 'Message de test numero '.$i.' suffisamment long.',
    ]);
    $response = $kernel->handle($request);
    echo $i.': '.$response->getStatusCode().' '.substr($response->getContent(), 0, 120).PHP_EOL;
}
