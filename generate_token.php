<?php

require_once 'config/database.php';
require_once 'includes/auth.php';

requireAdmin();

$token = bin2hex(random_bytes(32));

$stmt = $pdo->prepare(
    "UPDATE users
     SET api_token = ?
     WHERE id = ?"
);

$stmt->execute([
    $token,
    $_SESSION['user_id']
]);

echo "Your API token is:<br><br>";
echo htmlspecialchars($token);