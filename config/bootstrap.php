<?php
declare(strict_types=1);
require_once __DIR__ . '/supabase.php';

session_start([
    'use_strict_mode' => true,
    'use_only_cookies' => true,
    'cookie_httponly' => true,
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'cookie_samesite' => 'Lax',
]);
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; form-action 'self'; base-uri 'none'");
const STATUSES = ['Pending', 'In Progress', 'Completed'];

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $page): never
{
    header('Location: ' . $page, true, 303);
    exit;
}

function field(string $name): string
{
    return is_string($_POST[$name] ?? null) ? $_POST[$name] : '';
}

function csrf(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrfInput(): void
{
    echo '<input type="hidden" name="csrf" value="' . escape(csrf()) . '">';
}

function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        exit('Use the application form to submit this request.');
    }
    if (!hash_equals(csrf(), field('csrf'))) {
        http_response_code(403);
        exit('This form has expired. Reload the page and try again.');
    }
}

function flash(string $kind, string $message): void
{
    $_SESSION['flash'] = compact('kind', 'message');
}

function showFlash(): void
{
    $notice = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    if ($notice) {
        echo '<p class="notice ' . escape($notice['kind']) . '" role="status">'
            . escape($notice['message']) . '</p>';
    }
}

function saveAuth(array $data): void
{
    $_SESSION['auth'] = [
        'access' => $data['access_token'],
        'refresh' => $data['refresh_token'],
        'expires' => time() + (int) $data['expires_in'],
        'name' => $data['user']['user_metadata']['full_name'] ?? ($_SESSION['auth']['name'] ?? ''),
    ];
}

function requireUser(string $login = 'index.php'): string
{
    $auth = $_SESSION['auth'] ?? null;
    if (!$auth) {
        redirect($login);
    }
    if ($auth['expires'] <= time() + 30) {
        try {
            [$status, $data] = supabase('POST', '/auth/v1/token?grant_type=refresh_token', [
                'refresh_token' => $auth['refresh'],
            ]);
        } catch (RuntimeException) {
            http_response_code(503);
            exit('TaskFlow could not refresh your session. Reload the page to try again.');
        }
        if ($status !== 200 || empty($data['access_token'])) {
            if ($status >= 500 || $status === 429) {
                http_response_code(503);
                exit('Sign-in is temporarily unavailable. Reload the page to try again.');
            }
            unset($_SESSION['auth']);
            flash('error', 'Your session expired. Sign in to continue.');
            redirect($login);
        }
        saveAuth($data);
    }
    return $_SESSION['auth']['access'];
}

function taskRequest(string $method, string $query, ?array $body, string $token): array
{
    [$status, $data] = supabase($method, '/rest/v1/tasks?' . $query, $body, $token, ['Prefer: return=representation']);
    if ($status < 200 || $status >= 300) {
        error_log('TaskFlow task request returned HTTP ' . $status);
        throw new RuntimeException('TaskFlow could not save or load your tasks. Please try again.');
    }
    return $data;
}

function uuid(string $value): bool
{
    return (bool) preg_match('/^[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}$/iD', $value);
}

function length(string $value): int
{
    return preg_match_all('/./us', $value) ?: 0;
}
