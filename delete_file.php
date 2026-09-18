<?php

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: files.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Verify CSRF Token
|--------------------------------------------------------------------------
*/

verifyCsrfToken();

/*
|--------------------------------------------------------------------------
| Validate File ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {

    die('Invalid file ID.');
}

/*
|--------------------------------------------------------------------------
| Get File Information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT file_path
     FROM uploaded_files
     WHERE id = ?"
);

$stmt->execute([$id]);

$file = $stmt->fetch();

if (!$file) {

    die('File not found.');
}

/*
|--------------------------------------------------------------------------
| Delete Physical File
|--------------------------------------------------------------------------
*/

$fullPath = __DIR__ . '/' . $file['file_path'];

if (file_exists($fullPath)) {

    unlink($fullPath);
}

/*
|--------------------------------------------------------------------------
| Delete Database Record
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "DELETE FROM uploaded_files
     WHERE id = ?"
);

$stmt->execute([$id]);

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: files.php');
exit;