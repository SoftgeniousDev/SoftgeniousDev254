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
    WHERE 1=1
";

$params = [];

if ($search !== '') {

    $sql .= " AND (name LIKE ? OR email LIKE ?)";

    $searchTerm = "%$search%";

    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($role === 'admin' || $role === 'user') {

    $sql .= " AND role = ?";

    $params[] = $role;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>User Management | SoftgeniousDev</title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #111827;
        }

        /* =========================
           NAVBAR
        ========================= */

        .users-navbar {
            background: #0f172a;
            padding: 15px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow:
                0 2px 12px rgba(0, 0, 0, 0.15);
        }

        .users-brand {
            color: white;
            text-decoration: none;
            font-size: 22px;
            font-weight: 700;
        }

        .users-brand span {
            color: #60a5fa;
        }

        .users-nav {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .users-nav a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }

        .users-nav a:hover {
            color: white;
        }

        .users-nav .logout {
            color: #fca5a5;
        }

        /* =========================
           PAGE
        ========================= */

        .users-page {
            width: 92%;
            max-width: 1250px;
            margin: 35px auto;
        }

        /* =========================
           HEADER
        ========================= */

        .page-header {
            background:
                linear-gradient(
                    135deg,
                    #0f172a,
                    #1e3a8a
                );
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .header-content h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .header-content p {
            margin: 0;
            color: #cbd5e1;
            font-size: 14px;
        }

        .add-user-btn {
            display: inline-block;
            padding: 12px 18px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            transition: 0.2s;
        }

        .add-user-btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        /* =========================
           SEARCH CARD
        ========================= */

        .search-card {
            background: white;
            padding: 24px;
            border-radius: 14px;
            box-shadow:
                0 3px 15px rgba(0, 0, 0, 0.06);
            margin-bottom: 22px;
        }

        .search-title {
            margin: 0 0 18px;
            font-size: 18px;
        }

        .search-grid {
            display: grid;
            grid-template-columns: 1fr 220px;
            gap: 15px;
        }

        .search-field,
        .role-field {
            position: relative;
        }

        .search-field input,
        .role-field select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            margin: 0;
            background: white;
        }

        .search-field input:focus,
        .role-field select:focus {
            border-color: #2563eb;
            box-shadow:
                0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* =========================
           USERS CARD
        ========================= */

        .users-card {
            background: white;
            border-radius: 14px;
            box-shadow:
                0 3px 15px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .users-card-header {
            padding: 22px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .users-card-header h2 {
            margin: 0;
            font-size: 19px;
        }

        .user-count {
            padding: 6px 11px;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 15px 18px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 16px 18px;
            border-bottom: 1px solid #eef2f7;
            font-size: 14px;
            color: #374151;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        /* =========================
           USER ID
        ========================= */

        .user-id {
            color: #64748b;
            font-weight: 600;
        }

        /* =========================
           USER NAME
        ========================= */

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #dbeafe;
            color: #1d4ed8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .user-name {
            font-weight: 600;
            color: #111827;
        }

        /* =========================
           EMAIL
        ========================= */

        .email {
            color: #64748b;
        }

        /* =========================
           ROLE BADGES
        ========================= */

        .role-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .role-admin {
            background: #ede9fe;
            color: #6d28d9;
        }

        .role-user {
            background: #dcfce7;
            color: #15803d;
        }

        /* =========================
           DATE
        ========================= */

        .date {
            color: #64748b;
            font-size: 13px;
        }

        /* =========================
           ACTIONS
        ========================= */

        .actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .edit-btn {
            display: inline-block;
            padding: 7px 11px;
            border-radius: 6px;
            background: #eff6ff;
            color: #1d4ed8;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        .edit-btn:hover {
            background: #dbeafe;
        }

        .delete-btn {
            padding: 7px 11px;
            border: none;
            border-radius: 6px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: #fecaca;
        }

        /* =========================
           EMPTY STATE
        ========================= */

        .empty-state {
            text-align: center;
            padding: 55px 20px;
            color: #64748b;
        }

        .empty-icon {
            font-size: 40px;
            margin-bottom: 12px;
        }

        .empty-state h3 {
            margin: 0 0 6px;
            color: #374151;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        /* =========================
           FOOTER
        ========================= */

        .footer {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            margin: 25px 0;
        }

        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 800px) {

            .users-navbar {
                flex-direction: column;
                gap: 13px;
            }

            .users-nav {
                justify-content: center;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-grid {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 500px) {

            .users-page {
                width: 94%;
                margin: 22px auto;
            }

            .page-header {
                padding: 24px 20px;
            }

            .header-content h1 {
                font-size: 25px;
            }

            .search-card {
                padding: 20px;
            }

            .users-card-header {
                padding: 18px;
            }

        }

    </style>

</head>

<body>


<!-- =========================
     NAVIGATION
========================= -->

<nav class="users-navbar">

    <a
        href="dashboard.php"
        class="users-brand"
    >
        Softgenious<span>Dev</span>
    </a>


    <div class="users-nav">

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

        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</nav>


<!-- =========================
     MAIN
========================= -->

<main class="users-page">


    <!-- HEADER -->

    <section class="page-header">

        <div class="header-content">

            <h1>
                User Management
            </h1>

            <p>
                Manage accounts, roles and registered users.
            </p>

        </div>


        <a
            href="add_user.php"
            class="add-user-btn"
        >
            + Add New User
        </a>

    </section>


    <!-- SEARCH -->

    <section class="search-card">

        <h2 class="search-title">
            Search & Filter
        </h2>

        <div class="search-grid">

            <div class="search-field">

                <input
                    type="text"
                    name="search"
                    id="search"
                    placeholder="Search by name or email..."
                    value="<?= htmlspecialchars($search) ?>"
                >

            </div>


            <div class="role-field">

                <select
                    name="role"
                    id="role"
                >

                    <option value="">
                        All Roles
                    </option>

                    <option
                        value="admin"
                        <?= $role === 'admin' ? 'selected' : '' ?>
                    >
                        Administrators
                    </option>

                    <option
                        value="user"
                        <?= $role === 'user' ? 'selected' : '' ?>
                    >
                        Users
                    </option>

                </select>

            </div>

        </div>

    </section>


    <!-- USERS -->

    <section class="users-card">

        <div class="users-card-header">

            <h2>
                Registered Users
            </h2>

            <span class="user-count">
                <?= count($users) ?> users
            </span>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            User
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Role
                        </th>

                        <th>
                            Created
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody id="usersTable">

                    <?php if (empty($users)): ?>

                        <tr>

                            <td colspan="6">

                                <div class="empty-state">

                                    <div class="empty-icon">
                                        👥
                                    </div>

                                    <h3>
                                        No users found
                                    </h3>

                                    <p>
                                        Try changing your search or filter.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php else: ?>


                        <?php foreach ($users as $user): ?>

                            <?php

                            $initial =
                                strtoupper(
                                    substr(
                                        $user['name'],
                                        0,
                                        1
                                    )
                                );

                            ?>


                            <tr>

                                <!-- ID -->

                                <td>

                                    <span class="user-id">

                                        #<?= (int) $user['id'] ?>

                                    </span>

                                </td>


                                <!-- NAME -->

                                <td>

                                    <div class="user-info">

                                        <div class="avatar">

                                            <?= htmlspecialchars($initial) ?>

                                        </div>

                                        <div class="user-name">

                                            <?= htmlspecialchars(
                                                $user['name']
                                            ) ?>

                                        </div>

                                    </div>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <span class="email">

                                        <?= htmlspecialchars(
                                            $user['email']
                                        ) ?>

                                    </span>

                                </td>


                                <!-- ROLE -->

                                <td>

                                    <?php if (
                                        $user['role'] === 'admin'
                                    ): ?>

                                        <span
                                            class="role-badge role-admin"
                                        >
                                            Admin
                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="role-badge role-user"
                                        >
                                            User
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- CREATED -->

                                <td>

                                    <span class="date">

                                        <?= htmlspecialchars(
                                            $user['created_at']
                                        ) ?>

                                    </span>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="actions">


                                        <a
                                            href="edit_user.php?id=<?= (int) $user['id'] ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>


                                        <?php if (
                                            (int) $user['id']
                                            !==
                                            (int) $_SESSION['user_id']
                                        ): ?>


                                            <form
                                                method="POST"
                                                action="delete_user.php"
                                                onsubmit="return confirm('Are you sure you want to delete this user?');"
                                                style="margin:0;"
                                            >

                                                <?= csrfField() ?>

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $user['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="delete-btn"
                                                >
                                                    Delete
                                                </button>

                                            </form>


                                        <?php endif; ?>


                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>


                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>


    <div class="footer">

        © <?= date('Y') ?> SoftgeniousDev

    </div>


</main>


<!-- jQuery -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


<script>

$(document).ready(function() {

    function searchUsers() {

        let search = $('#search').val();

        let role = $('#role').val();


        $.ajax({

            url: 'search_users.php',

            type: 'GET',

            data: {
                search: search,
                role: role
            },

            success: function(response) {

                $('#usersTable').html(response);

            },

            error: function() {

                $('#usersTable').html(
                    '<tr><td colspan="6" style="text-align:center;padding:40px;">Unable to load users.</td></tr>'
                );

            }

        });

    }


    /* Search while typing */

    $('#search').on('keyup', function() {

        searchUsers();

    });


    /* Filter by role */

    $('#role').on('change', function() {

        searchUsers();

    });

});

</script>


</body>

</html>