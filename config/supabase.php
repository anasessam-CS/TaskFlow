<?php
declare(strict_types=1);

/** Send a request to Supabase Auth or PostgREST using the current user's token. */
function supabase(string $method, string $path, ?array $body = null, ?string $token = null, array $extraHeaders = []): array
{
    $file = __DIR__ . '/local.php';
    $local = is_file($file) ? require $file : [];
    $url = rtrim(getenv('SUPABASE_URL') ?: ($local['url'] ?? ''), '/');
    $key = getenv('SUPABASE_PUBLISHABLE_KEY') ?: ($local['key'] ?? '');
    if (!preg_match('~^https://[a-z0-9-]+\.supabase\.co$~D', $url) || !str_starts_with($key, 'sb_publishable_')) {
        throw new RuntimeException('Configure the Supabase project URL and publishable key first.');
    }

    $headers = ['apikey: ' . $key, 'Accept: application/json', ...$extraHeaders];
    if ($token !== null) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    $request = curl_init($url . $path);
    curl_setopt_array($request, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 12,
    ]);
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($body, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE));
    }
    curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($request);
    $status = curl_getinfo($request, CURLINFO_RESPONSE_CODE);
    if ($response === false) {
        throw new RuntimeException('Could not reach Supabase. Please try again.');
    }
    $data = $response === '' ? [] : json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE && $response !== '') {
        error_log('TaskFlow received an invalid response from Supabase (HTTP ' . $status . ').');
        throw new RuntimeException('Supabase returned an unexpected response. Please try again.');
    }
    return [$status, is_array($data) ? $data : []];
}
