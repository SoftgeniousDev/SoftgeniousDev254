<?php

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';

requireAdmin();

$stmt = $pdo->query(
    "SELECT 
        uploaded_files.id,
        uploaded_files.original_name,
        uploaded_files.file_path,
        uploaded_files.file_size,
        uploaded_files.file_type,
        uploaded_files.uploaded_at,
        users.name AS uploaded_by
     FROM uploaded_files
     INNER JOIN users
        ON uploaded_files.user_id = users.id
     ORDER BY uploaded_files.id DESC"
);

$files = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function formatFileSize(int $bytes): string
{
    if ($bytes >= 1024 * 1024) {
        return number_format($bytes / (1024 * 1024), 2) . ' MB';
    }

    return number_format($bytes / 1024, 2) . ' KB';
}

function getFileIcon(string $fileType, string $fileName): string
{
    $extension = strtolower(
        pathinfo($fileName, PATHINFO_EXTENSION)
    );

    if ($extension === 'pdf') {
        return 'PDF';
    }

    if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
        return 'IMG';
    }

    if (in_array($extension, ['doc', 'docx'], true)) {
        return 'DOC';
    }

    return 'FILE';
}

$totalFiles = count($files);

$totalStorage = 0;

foreach ($files as $file) {
    $totalStorage += (int) $file['file_size'];
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

    <title>Files | SoftgeniousDev</title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

    <style>

        /* =========================================================
           PAGE
        ========================================================= */

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        /* =========================================================
           NAVBAR
        ========================================================= */

        .topbar {
            background: #111827;
            min-height: 70px;
            display: flex;
            align-items: center;
            padding: 0 5%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
        }

        .brand {
            color: white;
            font-size: 21px;
            font-weight: 700;
            text-decoration: none;
            margin-right: 35px;
            letter-spacing: 0.3px;
        }

        .brand span {
            color: #38bdf8;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #d1d5db;
            text-decoration: none;
            padding: 10px 13px;
            border-radius: 7px;
            font-size: 14px;
            transition: 0.2s;
        }

        .nav-links a:hover {
            background: #1f2937;
            color: white;
        }

        .nav-links a.active {
            background: linear-gradient(
                135deg,
                #2563eb,
                #06b6d4
            );
            color: white;
        }

        /* =========================================================
           MAIN CONTAINER
        ========================================================= */

        .files-container {
            width: 92%;
            max-width: 1250px;
            margin: 40px auto;
        }

        /* =========================================================
           HEADER
        ========================================================= */

        .page-header {
            background: linear-gradient(
                135deg,
                #0f172a,
                #1e3a8a,
                #0891b2
            );

            color: white;
            padding: 35px;
            border-radius: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;

            box-shadow:
                0 15px 35px rgba(15, 23, 42, 0.18);

            margin-bottom: 25px;
        }

        .page-header h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .page-header p {
            margin: 0;
            color: #dbeafe;
            font-size: 15px;
        }

        .upload-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            background: white;
            color: #1d4ed8;

            text-decoration: none;

            padding: 13px 20px;
            border-radius: 9px;

            font-size: 14px;
            font-weight: 700;

            transition: 0.2s;
            white-space: nowrap;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow:
                0 8px 20px rgba(0, 0, 0, 0.15);
        }

        /* =========================================================
           STAT CARDS
        ========================================================= */

        .stats-grid {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 22px;

            border: 1px solid #e5e7eb;

            box-shadow:
                0 5px 18px rgba(15, 23, 42, 0.06);

            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: "";
            position: absolute;
            width: 80px;
            height: 80px;
            right: -30px;
            top: -30px;

            border-radius: 50%;

            background: #eff6ff;
        }

        .stat-label {
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-top: 8px;
            color: #111827;
        }

        .stat-description {
            margin-top: 5px;
            font-size: 13px;
            color: #9ca3af;
        }

        /* =========================================================
           FILE TABLE CARD
        ========================================================= */

        .files-card {
            background: white;
            border-radius: 16px;

            border: 1px solid #e5e7eb;

            box-shadow:
                0 8px 25px rgba(15, 23, 42, 0.06);

            overflow: hidden;
        }

        .files-card-header {
            padding: 22px 25px;

            display: flex;
            justify-content: space-between;
            align-items: center;

            border-bottom: 1px solid #eef0f3;
        }

        .files-card-header h2 {
            margin: 0;
            font-size: 19px;
            color: #111827;
        }

        .files-card-header p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 13px;
        }

        .file-count {
            background: #eff6ff;
            color: #2563eb;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        /* =========================================================
           TABLE
        ========================================================= */

        .table-wrapper {
            overflow-x: auto;
        }

        .files-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        .files-table th {
            background: #f8fafc;
            color: #64748b;

            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;

            padding: 14px 18px;

            text-align: left;

            border-bottom: 1px solid #e5e7eb;
        }

        .files-table td {
            padding: 16px 18px;

            border-bottom: 1px solid #f1f5f9;

            font-size: 14px;
            color: #374151;
        }

        .files-table tbody tr {
            transition: 0.2s;
        }

        .files-table tbody tr:hover {
            background: #f8fbff;
        }

        .files-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* =========================================================
           FILE NAME
        ========================================================= */

        .file-name-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .file-icon {
            width: 42px;
            height: 42px;

            border-radius: 10px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: linear-gradient(
                135deg,
                #dbeafe,
                #e0f2fe
            );

            color: #2563eb;

            font-size: 10px;
            font-weight: 800;
        }

        .file-name {
            font-weight: 600;
            color: #111827;
            max-width: 260px;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-id {
            color: #94a3b8;
            font-size: 12px;
        }

        /* =========================================================
           TYPE BADGE
        ========================================================= */

        .type-badge {
            display: inline-block;

            padding: 6px 9px;

            border-radius: 6px;

            background: #f1f5f9;

            color: #475569;

            font-size: 11px;
            font-weight: 700;

            max-width: 150px;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* =========================================================
           USER
        ========================================================= */

        .user-name {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .user-avatar {
            width: 30px;
            height: 30px;

            border-radius: 50%;

            background: linear-gradient(
                135deg,
                #2563eb,
                #06b6d4
            );

            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 12px;
            font-weight: 700;
        }

        /* =========================================================
           DATE
        ========================================================= */

        .date {
            color: #64748b;
            font-size: 13px;
        }

        /* =========================================================
           ACTIONS
        ========================================================= */

        .actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .action-btn {
            border: none;

            padding: 8px 12px;

            border-radius: 7px;

            font-size: 12px;
            font-weight: 600;

            text-decoration: none;

            cursor: pointer;

            transition: 0.2s;
        }

        .open-btn {
            background: #eff6ff;
            color: #2563eb;
        }

        .open-btn:hover {
            background: #dbeafe;
        }

        .delete-btn {
            background: #fef2f2;
            color: #dc2626;
        }

        .delete-btn:hover {
            background: #fee2e2;
        }

        .delete-form {
            display: inline;
            margin: 0;
        }

        /* =========================================================
           EMPTY STATE
        ========================================================= */

        .empty-state {
            padding: 70px 25px;
            text-align: center;
        }

        .empty-icon {
            width: 70px;
            height: 70px;

            margin: 0 auto 18px;

            border-radius: 18px;

            background: #eff6ff;

            color: #2563eb;

            display: flex;
            align-items: center;
            justify-content: center;

            font-weight: 800;
            font-size: 14px;
        }

        .empty-state h3 {
            margin: 0 0 7px;
            color: #111827;
        }

        .empty-state p {
            margin: 0 0 20px;
            color: #6b7280;
            font-size: 14px;
        }

        /* =========================================================
           FOOTER
        ========================================================= */

        .page-footer {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            margin-top: 25px;
            padding-bottom: 20px;
        }

        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 900px) {

            .topbar {
                padding: 15px 5%;
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }

            .brand {
                margin-right: 0;
            }

            .nav-links {
                width: 100%;
            }

            .nav-links a {
                font-size: 13px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .upload-btn {
                width: 100%;
            }

        }

        @media (max-width: 600px) {

            .files-container {
                width: 94%;
                margin: 25px auto;
            }

            .page-header {
                padding: 25px;
                border-radius: 14px;
            }

            .page-header h1 {
                font-size: 25px;
            }

            .files-card-header {
                padding: 18px;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVIGATION
========================================================= -->

<nav class="topbar">

    <a
        href="dashboard.php"
        class="brand"
    >
        Softgenious<span>Dev</span>
    </a>

    <div class="nav-links">

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

        <a
            href="files.php"
            class="active"
        >
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

    </div>

</nav>


<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<main class="files-container">


    <!-- PAGE HEADER -->

    <section class="page-header">

        <div>

            <h1>
                Uploaded Files
            </h1>

            <p>
                Manage, view and organize files stored in your system.
            </p>

        </div>


        <a
            href="upload_file.php"
            class="upload-btn"
        >
            + Upload New File
        </a>

    </section>


    <!-- STATISTICS -->

    <section class="stats-grid">


        <div class="stat-card">

            <div class="stat-label">
                Total Files
            </div>

            <div class="stat-value">
                <?= $totalFiles ?>
            </div>

            <div class="stat-description">
                Files currently stored
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                Storage Used
            </div>

            <div class="stat-value">
                <?= formatFileSize($totalStorage) ?>
            </div>

            <div class="stat-description">
                Total uploaded file size
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                Maximum File Size
            </div>

            <div class="stat-value">
                5 MB
            </div>

            <div class="stat-description">
                Maximum allowed upload
            </div>

        </div>


    </section>


    <!-- FILE TABLE -->

    <section class="files-card">


        <div class="files-card-header">

            <div>

                <h2>
                    File Library
                </h2>

                <p>
                    All files uploaded to the system.
                </p>

            </div>

            <div class="file-count">
                <?= $totalFiles ?> FILE<?= $totalFiles === 1 ? '' : 'S' ?>
            </div>

        </div>


        <?php if (empty($files)): ?>


            <!-- EMPTY STATE -->

            <div class="empty-state">

                <div class="empty-icon">
                    FILES
                </div>

                <h3>
                    No files uploaded
                </h3>

                <p>
                    Upload your first file to start building your file library.
                </p>

                <a
                    href="upload_file.php"
                    class="upload-btn"
                >
                    Upload File
                </a>

            </div>


        <?php else: ?>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table class="files-table">

                    <thead>

                        <tr>

                            <th>
                                File
                            </th>

                            <th>
                                Type
                            </th>

                            <th>
                                Size
                            </th>

                            <th>
                                Uploaded By
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach ($files as $file): ?>

                            <?php

                            $fileIcon = getFileIcon(
                                $file['file_type'],
                                $file['original_name']
                            );

                            $userName = $file['uploaded_by'];

                            $initial =
                                strtoupper(
                                    substr(
                                        $userName,
                                        0,
                                        1
                                    )
                                );

                            ?>


                            <tr>


                                <!-- FILE -->

                                <td>

                                    <div class="file-name-wrapper">

                                        <div class="file-icon">
                                            <?= htmlspecialchars($fileIcon) ?>
                                        </div>

                                        <div>

                                            <div class="file-name">

                                                <?= htmlspecialchars(
                                                    $file['original_name']
                                                ) ?>

                                            </div>

                                            <div class="file-id">

                                                ID #<?= (int) $file['id'] ?>

                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- TYPE -->

                                <td>

                                    <span class="type-badge">

                                        <?= htmlspecialchars(
                                            $file['file_type']
                                        ) ?>

                                    </span>

                                </td>


                                <!-- SIZE -->

                                <td>

                                    <?= formatFileSize(
                                        (int) $file['file_size']
                                    ) ?>

                                </td>


                                <!-- USER -->

                                <td>

                                    <div class="user-name">

                                        <div class="user-avatar">

                                            <?= htmlspecialchars(
                                                $initial
                                            ) ?>

                                        </div>

                                        <?= htmlspecialchars(
                                            $userName
                                        ) ?>

                                    </div>

                                </td>


                                <!-- DATE -->

                                <td>

                                    <span class="date">

                                        <?= htmlspecialchars(
                                            $file['uploaded_at']
                                        ) ?>

                                    </span>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="actions">


                                        <a
                                            href="<?= htmlspecialchars(
                                                $file['file_path']
                                            ) ?>"
                                            target="_blank"
                                            class="action-btn open-btn"
                                        >
                                            Open
                                        </a>


                                        <form
                                            method="POST"
                                            action="delete_file.php"
                                            class="delete-form"
                                            onsubmit="return confirm('Are you sure you want to delete this file?');"
                                        >

                                            <?= csrfField() ?>

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $file['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="action-btn delete-btn"
                                            >
                                                Delete
                                            </button>

                                        </form>


                                    </div>

                                </td>


                            </tr>

                        <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


        <?php endif; ?>


    </section>


    <div class="page-footer">

        SoftgeniousDev — tech for learning, creating and growing

    </div>


</main>


</body>

</html>