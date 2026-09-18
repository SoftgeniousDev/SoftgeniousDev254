<?php

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'vendor/autoload.php';

requireAdmin();

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/*
|--------------------------------------------------------------------------
| Get User ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {
    die('Invalid user ID.');
}

/*
|--------------------------------------------------------------------------
| Get User
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT id, name, email, role
     FROM users
     WHERE id = ?"
);

$stmt->execute([$id]);

$user = $stmt->fetch();

if (!$user) {
    die('User not found.');
}

/*
|--------------------------------------------------------------------------
| Data To Encode
|--------------------------------------------------------------------------
*/

$data = "Bayan Auth System\n"
      . "User ID: " . $user['id'] . "\n"
      . "Name: " . $user['name'] . "\n"
      . "Email: " . $user['email'] . "\n"
      . "Role: " . $user['role'];

/*
|--------------------------------------------------------------------------
| Create QR Code
|--------------------------------------------------------------------------
*/

$qrCode = new QrCode(
    data: $data,
    size: 300,
    margin: 10
);

/*
|--------------------------------------------------------------------------
| Write PNG
|--------------------------------------------------------------------------
*/

$writer = new PngWriter();

$result = $writer->write($qrCode);

/*
|--------------------------------------------------------------------------
| Display QR Code
|--------------------------------------------------------------------------
*/

header('Content-Type: ' . $result->getMimeType());

echo $result->getString();