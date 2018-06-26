<?php

require_once __DIR__ . '/../vendor/autoload.php';

$payload = json_decode(file_get_contents(__DIR__ . '/payload.json'));
$client = new \GuzzleHttp\Client([
    'base_uri' => 'http://vault:8200',
    'timeout'  => 2.0,
    'headers' => [
        'User-Agent' => 'dragonbe/hashicorp-vault/0.0.1 PHP 7.2',
        'Accept' => 'application/json',
    ]
]);

try {
    $loginResponse = $client->request('POST', '/v1/auth/approle/login', [
        'json' => [
            'role_id' => $payload->role_id,
            'secret_id' => $payload->secret_id,
        ],
    ]);
} catch (\GuzzleHttp\Exception\RequestException $requestException) {
    echo 'Unable to get credentials from Vault';
    exit;
} catch (\GuzzleHttp\Exception\ConnectException $connectException) {
    echo 'Unable to connect to Vault';
    exit;
}

$jsonResponse = (string) $loginResponse->getBody();
$response = json_decode($jsonResponse);
echo 'Client token: ' . $response->auth->client_token . '<br>' . PHP_EOL;
echo 'Policies: ' . implode(', ', $response->auth->policies) . '<br>' . PHP_EOL;
echo 'Lease time: ' . $response->auth->lease_duration . '<br>' . PHP_EOL;

file_put_contents(__DIR__ . '/.client', $response->auth->client_token);

echo '<a href="/vault.php">Go to DB connection</a>';