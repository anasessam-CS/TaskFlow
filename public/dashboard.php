<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../views/layout.php';
$token = requireUser();
$tasks = [];
$error = null;

try {
    // Supabase can cap each response. Advance by the number of rows actually returned.
    $offset = 0;
    do {
        $page = taskRequest('GET', 'order=created_at.desc,id.desc&limit=100&offset=' . $offset, null, $token);
        array_push($tasks, ...$page);
        $offset += count($page);
    } while ($page);
} catch (RuntimeException $exception) {
    $error = $exception->getMessage();
    http_response_code(503);
}
$counts = array_fill_keys(STATUSES, 0);
foreach ($tasks as $task) {
    $counts[$task['status']]++;
}
$name = $_SESSION['auth']['name'];
pageStart('Dashboard');
?>
<header class="topbar">
    <div class="container topbar-inner">
        <a href="dashboard.php" class="brand-link"><?php brand(); ?></a>
        <form action="logout.php" method="post">
            <?php csrfInput(); ?>
            <button class="button secondary">Sign out</button>
        </form>
    </div>
</header>
<main class="container dashboard">
    <div class="page-heading">
        <div>
            <h1><?= $name === '' ? 'Your dashboard' : 'Welcome, ' . escape($name) ?></h1>
            <p class="muted">Track your work and see what needs your attention.</p>
        </div>
        <a class="button primary" href="task.php"><span aria-hidden="true">＋</span> New task</a>
    </div>
    <?php showFlash(); ?>
    <?php if ($error): ?>
        <p class="notice error" role="alert"><?= escape($error) ?></p>
    <?php else: ?>
    <section class="stats" aria-label="Task statistics">
        <?php foreach (['Total tasks' => count($tasks), ...$counts] as $label => $count): ?>
            <article class="stat"><p><?= escape($label) ?></p><strong data-stat="<?= escape($label) ?>"><?= $count ?></strong></article>
        <?php endforeach; ?>
    </section>
    <section class="tasks" aria-labelledby="tasks-title">
        <div class="tasks-heading"><h2 id="tasks-title">Your tasks</h2></div>
        <?php if (!$tasks): ?>
            <div class="empty"><span class="brand-mark" aria-hidden="true">✓</span><h3>No tasks yet</h3><p class="muted">Add your first task to get started.</p></div>
        <?php else: ?>
            <ul class="task-list">
            <?php foreach ($tasks as $task): ?>
                <li class="task-row" data-task-id="<?= escape($task['id']) ?>">
                    <div class="task-info">
                        <h3><?= escape($task['title']) ?></h3>
                        <?php if ($task['description'] !== ''): ?><p class="description"><?= escape($task['description']) ?></p><?php endif; ?>
                        <p class="task-dates">
                            <?= $task['due_date'] ? 'Due ' . escape($task['due_date']) : 'No due date' ?>
                            <span aria-hidden="true">·</span> Added <?= escape(substr($task['created_at'], 0, 10)) ?>
                        </p>
                    </div>
                    <div class="task-actions">
                        <form method="post" action="api/tasks.php" data-status-form>
                            <?php csrfInput(); ?>
                            <input type="hidden" name="action" value="status">
                            <input type="hidden" name="id" value="<?= escape($task['id']) ?>">
                            <select name="status" class="status status-<?= strtolower(str_replace(' ', '-', $task['status'])) ?>" aria-label="Status for <?= escape($task['title']) ?>">
                                <?php foreach (STATUSES as $status): ?>
                                    <option <?= $status === $task['status'] ? 'selected' : '' ?>><?= escape($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="button secondary status-save">Save status</button>
                        </form>
                        <a class="button secondary" href="task.php?id=<?= escape($task['id']) ?>" aria-label="Edit <?= escape($task['title']) ?>">Edit</a>
                        <form method="post" action="api/tasks.php" data-confirm-delete>
                            <?php csrfInput(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= escape($task['id']) ?>">
                            <button class="button danger" aria-label="Delete <?= escape($task['title']) ?>">Delete</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</main>
<?php pageEnd(); ?>
