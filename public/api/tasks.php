<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';
requirePost();
$token = requireUser('../index.php');
$action = field('action');
$id = field('id');
if (!in_array($action, ['create', 'update', 'delete', 'status'], true)
    || ($action !== 'create' && !uuid($id))) {
    http_response_code(400);
    exit('Invalid task action or ID.');
}
$back = match ($action) {
    'create' => '../task.php',
    'update' => '../task.php?id=' . rawurlencode($id),
    default => '../dashboard.php',
};
$status = field('status');
$title = trim(field('title'));
$description = trim(field('description'));
$due = field('due_date');
$date = $due === '' || str_contains($due, chr(0)) ? null : DateTimeImmutable::createFromFormat('!Y-m-d', $due);
$data = ['title' => $title, 'description' => $description, 'status' => $status, 'due_date' => $due ?: null];
$editing = in_array($action, ['create', 'update'], true);
$error = match (true) {
    $editing && ($title === '' || length($title) > 120) => 'Enter a task title of up to 120 characters.',
    $editing && length($description) > 2000 => 'Use at most 2,000 characters for the description.',
    $editing && $due !== '' && (!$date || $date->format('Y-m-d') !== $due || str_starts_with($due, '0000')) => 'Enter a valid due date.',
    $action !== 'delete' && !in_array($status, STATUSES, true) => 'Choose a valid status.',
    default => null,
};

if ($error === null) {
    try {
        $query = 'id=eq.' . rawurlencode($id);
        $rows = match ($action) {
            'create' => taskRequest('POST', 'select=id', $data, $token),
            'update' => taskRequest('PATCH', $query, $data, $token),
            'status' => taskRequest('PATCH', $query, ['status' => $status], $token),
            'delete' => taskRequest('DELETE', $query, null, $token),
        };
        if (!$rows) {
            flash('error', 'That task could not be found.');
            redirect('../dashboard.php');
        }
        flash('success', match ($action) {
            'create' => 'Task added.',
            'delete' => 'Task deleted.',
            'status' => 'Status updated.',
            'update' => 'Task updated.',
        });
        redirect('../dashboard.php');
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    }
}
if ($editing) {
    $_SESSION['task_input'] = ['id' => $id, 'fields' => $data];
}
flash('error', $error);
redirect($back);
