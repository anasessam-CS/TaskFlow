<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../views/layout.php';
$token = requireUser();
$id = $_GET['id'] ?? '';
$editing = $id !== '';
$task = ['title' => '', 'description' => '', 'status' => 'Pending', 'due_date' => null];
if ($editing) {
    try {
        $rows = is_string($id) && uuid($id)
            ? taskRequest('GET', 'id=eq.' . rawurlencode($id), null, $token)
            : [];
        if (!$rows) {
            flash('error', 'That task could not be found.');
            redirect('dashboard.php');
        }
        $task = $rows[0];
    } catch (RuntimeException $exception) {
        flash('error', $exception->getMessage());
        redirect('dashboard.php');
    }
}
$input = $_SESSION['task_input'] ?? null;
unset($_SESSION['task_input']);
if ($input && $input['id'] === $id) {
    $task = $input['fields'];
}
$title = $editing ? 'Edit task' : 'New task';
pageStart($title);
?>
<header class="topbar"><div class="container topbar-inner">
    <a href="dashboard.php" class="brand-link"><?php brand(); ?></a>
    <a href="dashboard.php" class="button secondary">Dashboard</a>
</div></header>
<main class="container editor">
    <h1><?= escape($title) ?></h1>
    <p class="muted">Give your task a title and set its status.</p>
    <?php showFlash(); ?>
    <form method="post" action="api/tasks.php" class="form-stack editor-card">
        <?php csrfInput(); ?>
        <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= escape($id) ?>"><?php endif; ?>
        <label>Task title<input name="title" required maxlength="120" value="<?= escape($task['title']) ?>"></label>
        <label>Description <span class="optional">(optional)</span><textarea name="description" maxlength="2000" rows="5"><?= escape($task['description']) ?></textarea></label>
        <div class="form-columns">
            <label>Status<select name="status">
                <?php foreach (STATUSES as $status): ?>
                    <option <?= $task['status'] === $status ? 'selected' : '' ?>><?= escape($status) ?></option>
                <?php endforeach; ?>
            </select></label>
            <label>Due date <span class="optional">(optional)</span><input type="date" name="due_date" value="<?= escape($task['due_date'] ?? '') ?>"></label>
        </div>
        <div class="form-actions">
            <a href="dashboard.php" class="button secondary">Cancel</a>
            <button class="button primary"><?= $editing ? 'Save changes' : 'Add task' ?></button>
        </div>
    </form>
</main>
<?php pageEnd(); ?>
