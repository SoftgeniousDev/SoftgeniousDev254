<?php

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';

requireAdmin();

$search = trim($_GET['search'] ?? '');
$role = $_GET['role'] ?? '';

$sql = "
    SELECT id, name, email, role, created_at
    FROM users
    WHERE (name LIKE ? OR email LIKE ?)
";

$params = [];

$searchTerm = "%$search%";

$params[] = $searchTerm;
$params[] = $searchTerm;

if ($role === 'admin' || $role === 'user') {

    $sql .= " AND role = ?";

    $params[] = $role;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$users = $stmt->fetchAll();


if (empty($users)) {

    echo '<tr>';

    echo '<td colspan="6" style="text-align:center;">';

    echo 'No users found.';

    echo '</td>';

    echo '</tr>';

    exit;
}


foreach ($users as $user) {

    echo '<tr>';

    // ID
    echo '<td>';
    echo htmlspecialchars($user['id']);
    echo '</td>';


    // Name
    echo '<td>';
    echo htmlspecialchars($user['name']);
    echo '</td>';


    // Email
    echo '<td>';
    echo htmlspecialchars($user['email']);
    echo '</td>';


    // Role
    echo '<td>';
    echo htmlspecialchars($user['role']);
    echo '</td>';


    // Created date
    echo '<td>';
    echo htmlspecialchars($user['created_at']);
    echo '</td>';


    // Actions
    echo '<td>';


    // Edit
    echo '<a
            href="edit_user.php?id='
            . (int) $user['id']
            . '"
            class="btn btn-primary"
          >
            Edit
          </a> ';


    // Delete
    if (
        (int) $user['id']
        !==
        (int) $_SESSION['user_id']
    ) {

        echo '<form
                method="POST"
                action="delete_user.php"
                style="display:inline;"
                onsubmit="return confirm(\'Are you sure you want to delete this user?\');"
              >';


        // CSRF token
        echo csrfField();


        // User ID
        echo '<input
                type="hidden"
                name="id"
                value="'
                . (int) $user['id']
                . '"
              >';


        // Delete button
        echo '<button
                type="submit"
              >
                Delete
              </button>';


        echo '</form>';
    }


    echo '</td>';

    echo '</tr>';
}