<?php

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';

requireAdmin();

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


$error = '';
$success = '';


/*
|--------------------------------------------------------------------------
| Update User
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrfToken();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';

    if ($name === '') {

        $error = 'Name is required.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } elseif (
        $role !== 'admin' &&
        $role !== 'user'
    ) {

        $error = 'Invalid role selected.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check Duplicate Email
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             AND id != ?"
        );

        $stmt->execute([
            $email,
            $id
        ]);

        if ($stmt->fetch()) {

            $error = 'Email address is already in use.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Prevent Admin From Removing Their Own Admin Role
            |--------------------------------------------------------------------------
            */

            if (
                $id === (int) $_SESSION['user_id'] &&
                $role !== 'admin'
            ) {

                $error =
                    'You cannot remove your own admin role.';

            } else {

                $stmt = $pdo->prepare(
                    "UPDATE users
                     SET name = ?,
                         email = ?,
                         role = ?
                     WHERE id = ?"
                );

                $stmt->execute([
                    $name,
                    $email,
                    $role,
                    $id
                ]);

                /*
                |--------------------------------------------------------------------------
                | Update Current Session
                |--------------------------------------------------------------------------
                */

                if (
                    $id === (int) $_SESSION['user_id']
                ) {

                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $role;
                }

                $success = 'User updated successfully.';

                /*
                |--------------------------------------------------------------------------
                | Refresh User Data
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare(
                    "SELECT id, name, email, role
                     FROM users
                     WHERE id = ?"
                );

                $stmt->execute([$id]);

                $user = $stmt->fetch();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit User - Bayan Auth System</title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

</head>

<body>

<nav class="navbar">

    <a href="dashboard.php">
        Dashboard
    </a>

    <a href="users.php">
        Users
    </a>

    <a href="add_user.php">
        Add User
    </a>

    <a href="upload_file.php">
        Upload File
    </a>

    <a href="files.php">
        Files
    </a>

    <a
        href="generate_users_pdf.php"
        target="_blank"
    >
        PDF Report
    </a>

    <a href="logout.php">
        Logout
    </a>

</nav>


<div class="container">

    <div class="card">

        <h1>
            Edit User
        </h1>

        <?php if ($error): ?>

            <p style="color: red;">
                <?= htmlspecialchars($error) ?>
            </p>

        <?php endif; ?>


        <?php if ($success): ?>

            <p style="color: green;">
                <?= htmlspecialchars($success) ?>
            </p>

        <?php endif; ?>


        <form method="POST">

            <?= csrfField() ?>


            <label for="name">
                Name
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($user['name']) ?>"
                required
            >


            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($user['email']) ?>"
                required
            >


            <label for="role">
                Role
            </label>

            <select
                id="role"
                name="role"
                required
            >

                <option
                    value="user"
                    <?= $user['role'] === 'user'
                        ? 'selected'
                        : '' ?>
                >
                    User
                </option>

                <option
                    value="admin"
                    <?= $user['role'] === 'admin'
                        ? 'selected'
                        : '' ?>
                >
                    Admin
                </option>

            </select>


            <button
                type="submit"
                class="btn btn-primary"
            >
                Update User
            </button>

            <a
                href="users.php"
                class="btn"
            >
                Cancel
            </a>

        </form>

    </div>

</div>

</body>

</html>
