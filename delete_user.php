<?php

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

verifyCsrfToken();

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {
    die('Invalid user ID.');
}

if ($id === (int) $_SESSION['user_id']) {
    die('You cannot delete your own account.');
}

$stmt = $pdo->prepare(
    "DELETE FROM users WHERE id = ?"
);

$stmt->execute([$id]);

header('Location: users.php');
exit;