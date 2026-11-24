<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
requirePost();
$token = $_SESSION['auth']['access'] ?? null;
if ($token) {
    try {
        supabase('POST', '/auth/v1/logout?scope=local', null, $token);
    } catch (RuntimeException) {
        error_log('TaskFlow could not revoke the remote session during logout.');
    }
}
$_SESSION = [];
session_regenerate_id(true);
flash('success', 'You have signed out.');
redirect('index.php');
