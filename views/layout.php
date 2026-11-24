<?php
function pageStart(string $title, string $class = ''): void
{
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escape($title) ?> · TaskFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body class="<?= escape($class) ?>">
<?php
}

function brand(): void
{
    echo '<span class="brand"><span class="brand-mark" aria-hidden="true">✓</span>TaskFlow</span>';
}

function pageEnd(): void
{
    echo '</body></html>';
}
