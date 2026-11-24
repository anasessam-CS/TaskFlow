<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';
requirePost();
if (isset($_SESSION['auth'])) {
    redirect('../dashboard.php');
}

$action = field('action');
if (!in_array($action, ['login', 'register'], true)) {
    http_response_code(400);
    exit('Unknown authentication action.');
}
$register = $action === 'register';
$back = $register ? '../register.php' : '../index.php';
$email = trim(field('email'));
$password = field('password');
$name = trim(field('full_name'));
$_SESSION['auth_input'] = ['email' => $email, 'full_name' => $name];

$error = match (true) {
    $email === '' || $password === '' => 'Enter your email and password.',
    !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 => 'Enter a valid email address.',
    $register && ($name === '' || length($name) > 80) => 'Enter a name of up to 80 characters.',
    $register && (strlen($password) < 8 || strlen($password) > 72) => 'Use a password between 8 and 72 bytes.',
    $register && $password !== field('confirm_password') => 'The passwords do not match.',
    default => null,
};
if ($error) {
    flash('error', $error);
    redirect($back);
}

try {
    $payload = ['email' => $email, 'password' => $password];
    if ($register) {
        $payload['data'] = ['full_name' => $name];
        [$status, $data] = supabase('POST', '/auth/v1/signup', $payload);
    } else {
        [$status, $data] = supabase('POST', '/auth/v1/token?grant_type=password', $payload);
    }
    if ($status === 429) {
        flash('error', 'Too many attempts. Wait a moment and try again.');
        redirect($back);
    }
    if ($status >= 500) {
        flash('error', 'Sign-in is temporarily unavailable. Please try again.');
        redirect($back);
    }
    if ($status >= 200 && $status < 300 && !empty($data['access_token'])) {
        session_regenerate_id(true);
        unset($_SESSION['csrf'], $_SESSION['auth_input']);
        saveAuth($data);
        redirect('../dashboard.php');
    }
    if ($register && (($status >= 200 && $status < 300)
        || in_array($data['code'] ?? '', ['user_already_exists', 'email_exists'], true))) {
        unset($_SESSION['auth_input']);
        flash('success', 'If this address can be registered, check your inbox to confirm it. Already have an account? Sign in.');
        redirect('../index.php');
    }
    $error = $register ? 'Registration failed. Check the email and password requirements.'
        : 'Sign-in failed. Check your email, password and email confirmation.';
} catch (RuntimeException $exception) {
    $error = $exception->getMessage();
}
flash('error', $error);
redirect($back);
