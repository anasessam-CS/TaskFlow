<?php
require_once __DIR__ . '/layout.php';
if (isset($_SESSION['auth'])) {
    redirect('dashboard.php');
}
$input = $_SESSION['auth_input'] ?? [];
unset($_SESSION['auth_input']);
pageStart($register ? 'Create account' : 'Sign in', 'auth-page');
?>
<main class="auth-shell">
    <section class="auth-panel">
        <?php brand(); ?>
        <div class="auth-intro">
            <h2>Make progress<br>every day.</h2>
            <p>Organize your tasks, keep track of deadlines, and see what you’ve accomplished.</p>
        </div>
        <div class="auth-art" aria-hidden="true">
            <div class="art-row"><span>✓</span><i></i></div>
            <div class="art-row"><span>✓</span><i></i></div>
            <div class="art-row"><span></span><i></i></div>
        </div>
    </section>
    <section class="auth-main">
        <h1><?= $register ? 'Create your account' : 'Welcome back' ?></h1>
        <p class="muted"><?= $register ? 'Enter your details to get started.' : 'Sign in to manage your tasks.' ?></p>
        <?php showFlash(); ?>
        <form method="post" action="api/auth.php" class="form-stack">
            <?php csrfInput(); ?>
            <input type="hidden" name="action" value="<?= $register ? 'register' : 'login' ?>">
            <?php if ($register): ?>
                <label>Full name<input name="full_name" autocomplete="name" maxlength="80" required value="<?= escape($input['full_name'] ?? '') ?>"></label>
            <?php endif; ?>
            <label>Email address<input type="email" name="email" autocomplete="username" maxlength="254" required placeholder="you@example.com" value="<?= escape($input['email'] ?? '') ?>"></label>
            <label>Password<input type="password" name="password" autocomplete="<?= $register ? 'new-password' : 'current-password' ?>" <?= $register ? 'minlength="8" maxlength="72"' : '' ?> required></label>
            <?php if ($register): ?>
                <label>Confirm password<input type="password" name="confirm_password" autocomplete="new-password" required></label>
            <?php endif; ?>
            <button class="button primary"><?= $register ? 'Create account' : 'Sign in' ?></button>
        </form>
        <p class="auth-switch"><?= $register ? 'Already have an account?' : 'New to TaskFlow?' ?>
            <a href="<?= $register ? 'index.php' : 'register.php' ?>"><?= $register ? 'Sign in' : 'Create an account' ?></a>
        </p>
    </section>
</main>
<?php pageEnd(); ?>
