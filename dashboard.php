<?php

require_once 'config/error.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$isAdmin = $_SESSION['role'] === 'admin';

$name = htmlspecialchars($_SESSION['name']);
$email = htmlspecialchars($_SESSION['email']);
$role = htmlspecialchars($_SESSION['role']);

$initial = strtoupper(
    substr($_SESSION['name'], 0, 1)
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard | SoftgeniousDev</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family:
                Inter,
                Arial,
                Helvetica,
                sans-serif;

            background: #f6f8fc;
            color: #172033;
        }

        a {
            text-decoration: none;
        }


        /* =================================
           SIDEBAR
        ================================= */

        .sidebar {
            position: fixed;

            left: 0;
            top: 0;
            bottom: 0;

            width: 245px;

            background: #111827;

            padding: 25px 16px;

            z-index: 1000;
        }


        .brand {
            display: flex;
            align-items: center;

            gap: 11px;

            padding: 5px 12px 30px;
        }


        .brand-logo {
            width: 38px;
            height: 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #06b6d4
                );

            color: white;

            font-size: 14px;
            font-weight: 800;
        }


        .brand-name {
            color: white;

            font-size: 18px;
            font-weight: 800;
        }


        .brand-name span {
            color: #38bdf8;
        }


        .menu-title {
            color: #64748b;

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 1.3px;

            padding: 0 12px;

            margin: 10px 0 10px;
        }


        .nav-menu {
            display: flex;

            flex-direction: column;

            gap: 5px;
        }


        .nav-link {
            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px;

            border-radius: 9px;

            color: #9ca3af;

            font-size: 13px;

            font-weight: 600;

            transition: 0.2s ease;
        }


        .nav-link:hover {
            background: #1f2937;

            color: white;
        }


        .nav-link.active {
            background: #1d4ed8;

            color: white;
        }


        .nav-icon {
            width: 30px;
            height: 30px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 8px;

            background: rgba(
                255,
                255,
                255,
                0.06
            );

            font-size: 10px;

            font-weight: 800;

            letter-spacing: 0.4px;
        }


        .nav-link.active .nav-icon {
            background: rgba(
                255,
                255,
                255,
                0.14
            );
        }


        .sidebar-bottom {
            position: absolute;

            left: 16px;
            right: 16px;
            bottom: 20px;

            border-top: 1px solid #1f2937;

            padding-top: 15px;
        }


        .logout {
            color: #f87171;
        }


        .logout:hover {
            color: #fecaca;
            background: #1f2937;
        }


        /* =================================
           MAIN
        ================================= */

        .main {
            margin-left: 245px;

            min-height: 100vh;

            padding: 30px 35px;
        }


        .topbar {
            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 28px;
        }


        .topbar-title h1 {
            margin: 0;

            font-size: 25px;

            color: #111827;
        }


        .topbar-title p {
            margin: 6px 0 0;

            color: #64748b;

            font-size: 13px;
        }


        .user-mini {
            display: flex;

            align-items: center;

            gap: 11px;
        }


        .mini-avatar {
            width: 40px;
            height: 40px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #06b6d4
                );

            color: white;

            font-size: 14px;

            font-weight: 800;
        }


        .mini-info strong {
            display: block;

            color: #111827;

            font-size: 13px;
        }


        .mini-info span {
            color: #64748b;

            font-size: 11px;
        }


        /* =================================
           WELCOME
        ================================= */

        .welcome {
            position: relative;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #111827,
                    #1e3a8a
                );

            border-radius: 18px;

            padding: 32px;

            color: white;

            margin-bottom: 25px;

            box-shadow:
                0 12px 30px
                rgba(30, 58, 138, 0.15);
        }


        .welcome::before {
            content: "";

            position: absolute;

            width: 240px;
            height: 240px;

            border-radius: 50%;

            border: 35px solid
                rgba(255, 255, 255, 0.04);

            right: -80px;
            top: -100px;
        }


        .welcome::after {
            content: "";

            position: absolute;

            width: 100px;
            height: 100px;

            border-radius: 50%;

            background:
                rgba(56, 189, 248, 0.12);

            right: 130px;
            bottom: -60px;
        }


        .welcome-content {
            position: relative;

            z-index: 2;
        }


        .welcome-label {
            color: #93c5fd;

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 1.5px;

            text-transform: uppercase;

            margin-bottom: 10px;
        }


        .welcome h2 {
            margin: 0 0 8px;

            font-size: 29px;
        }


        .welcome p {
            margin: 0;

            color: #cbd5e1;

            font-size: 14px;
        }


        /* =================================
           STATISTICS
        ================================= */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 25px;
        }


        .stat-card {
            background: white;

            border: 1px solid #e5e7eb;

            border-radius: 15px;

            padding: 20px;

            transition: 0.2s ease;
        }


        .stat-card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 10px 25px
                rgba(15, 23, 42, 0.07);
        }


        .stat-top {
            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 17px;
        }


        .stat-icon {
            width: 40px;
            height: 40px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background: #eff6ff;

            color: #2563eb;

            font-size: 10px;

            font-weight: 800;

            border: 1px solid #dbeafe;
        }


        .stat-line {
            width: 28px;
            height: 3px;

            border-radius: 5px;

            background: #dbeafe;
        }


        .stat-title {
            color: #64748b;

            font-size: 11px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            margin-bottom: 6px;
        }


        .stat-value {
            color: #111827;

            font-size: 17px;

            font-weight: 800;
        }


        /* =================================
           CONTENT
        ================================= */

        .content-grid {
            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 20px;
        }


        .card {
            background: white;

            border: 1px solid #e5e7eb;

            border-radius: 16px;

            padding: 24px;
        }


        .card-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 20px;
        }


        .card-header h3 {
            margin: 0;

            font-size: 17px;

            color: #111827;
        }


        .card-header span {
            color: #94a3b8;

            font-size: 11px;
        }


        /* =================================
           PROFILE
        ================================= */

        .profile {
            display: flex;

            align-items: center;

            gap: 15px;

            padding: 17px;

            background: #f8fafc;

            border-radius: 12px;

            margin-bottom: 17px;
        }


        .profile-avatar {
            width: 54px;
            height: 54px;

            flex-shrink: 0;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #06b6d4
                );

            color: white;

            font-size: 20px;

            font-weight: 800;
        }


        .profile-details h4 {
            margin: 0 0 4px;

            font-size: 15px;

            color: #111827;
        }


        .profile-details p {
            margin: 0;

            color: #64748b;

            font-size: 12px;
        }


        .role {
            display: inline-block;

            margin-top: 7px;

            padding: 4px 9px;

            border-radius: 20px;

            background: #dbeafe;

            color: #1d4ed8;

            font-size: 9px;

            font-weight: 800;

            text-transform: uppercase;
        }


        .description {
            color: #64748b;

            font-size: 13px;

            line-height: 1.7;

            margin: 0;
        }


        /* =================================
           QUICK ACTIONS
        ================================= */

        .actions {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 11px;
        }


        .action {
            display: flex;

            align-items: center;

            gap: 11px;

            padding: 14px;

            border: 1px solid #e5e7eb;

            border-radius: 11px;

            color: #1e293b;

            background: #fff;

            transition: 0.2s ease;
        }


        .action:hover {
            border-color: #93c5fd;

            background: #f8fbff;

            transform: translateY(-2px);
        }


        .action-icon {
            width: 34px;
            height: 34px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 8px;

            background: #eff6ff;

            color: #2563eb;

            font-size: 11px;

            font-weight: 800;
        }


        .action-text strong {
            display: block;

            font-size: 12px;

            margin-bottom: 3px;
        }


        .action-text span {
            color: #94a3b8;

            font-size: 10px;
        }


        /* =================================
           FEATURES
        ================================= */

        .features {
            display: grid;

            gap: 10px;
        }


        .feature {
            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px;

            border-radius: 10px;

            background: #f8fafc;
        }


        .feature-icon {
            width: 34px;
            height: 34px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 8px;

            background: white;

            border: 1px solid #e5e7eb;

            color: #2563eb;

            font-size: 9px;

            font-weight: 800;
        }


        .feature-text strong {
            display: block;

            font-size: 12px;

            color: #334155;

            margin-bottom: 3px;
        }


        .feature-text span {
            color: #94a3b8;

            font-size: 10px;
        }


        /* =================================
           REPORT CARD
        ================================= */

        .report-card {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;
        }


        .report-text h3 {
            margin: 0 0 7px;

            font-size: 17px;
        }


        .report-text p {
            margin: 0;

            color: #64748b;

            font-size: 12px;

            line-height: 1.6;
        }


        .report-btn {
            flex-shrink: 0;

            display: inline-block;

            padding: 11px 17px;

            border-radius: 8px;

            background: #2563eb;

            color: white;

            font-size: 12px;

            font-weight: 700;

            transition: 0.2s ease;
        }


        .report-btn:hover {
            background: #1d4ed8;
        }


        /* =================================
           FOOTER
        ================================= */

        .footer {
            text-align: center;

            padding: 30px 0 5px;

            color: #94a3b8;

            font-size: 11px;
        }


        .footer strong {
            color: #64748b;
        }


        /* =================================
           RESPONSIVE
        ================================= */

        @media (max-width: 1050px) {

            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 800px) {

            .sidebar {
                position: relative;

                width: 100%;

                height: auto;

                padding: 15px;
            }


            .brand {
                padding-bottom: 15px;
            }


            .nav-menu {
                flex-direction: row;

                overflow-x: auto;
            }


            .menu-title {
                display: none;
            }


            .nav-link {
                white-space: nowrap;
            }


            .sidebar-bottom {
                position: static;

                margin-top: 10px;

                border-top: 1px solid #1f2937;
            }


            .main {
                margin-left: 0;

                padding: 22px 18px;
            }


            .content-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 550px) {

            .stats {
                grid-template-columns: 1fr;
            }


            .topbar {
                align-items: flex-start;
            }


            .mini-info {
                display: none;
            }


            .welcome {
                padding: 25px 21px;
            }


            .welcome h2 {
                font-size: 24px;
            }


            .actions {
                grid-template-columns: 1fr;
            }


            .report-card {
                align-items: flex-start;

                flex-direction: column;
            }

        }

    </style>

</head>


<body>


<!-- =================================
     SIDEBAR
================================= -->

<aside class="sidebar">


    <div class="brand">

        <div class="brand-logo">
            SG
        </div>

        <div class="brand-name">
            Softgenious<span>Dev</span>
        </div>

    </div>


    <div class="menu-title">
        MAIN MENU
    </div>


    <nav class="nav-menu">

        <a
            href="dashboard.php"
            class="nav-link active"
        >

            <span class="nav-icon">
                DB
            </span>

            Dashboard

        </a>


        <?php if ($isAdmin): ?>

            <a
                href="users.php"
                class="nav-link"
            >

                <span class="nav-icon">
                    US
                </span>

                Users

            </a>


            <a
                href="add_user.php"
                class="nav-link"
            >

                <span class="nav-icon">
                    +
                </span>

                Add User

            </a>


            <a
                href="upload_file.php"
                class="nav-link"
            >

                <span class="nav-icon">
                    UP
                </span>

                Upload File

            </a>


            <a
                href="files.php"
                class="nav-link"
            >

                <span class="nav-icon">
                    FL
                </span>

                Files

            </a>


            <a
                href="generate_users_pdf.php"
                target="_blank"
                class="nav-link"
            >

                <span class="nav-icon">
                    RP
                </span>

                Reports

            </a>

        <?php endif; ?>

    </nav>


    <div class="sidebar-bottom">

        <a
            href="logout.php"
            class="nav-link logout"
        >

            <span class="nav-icon">
                LO
            </span>

            Logout

        </a>

    </div>


</aside>


<!-- =================================
     MAIN CONTENT
================================= -->

<main class="main">


    <!-- TOP BAR -->

    <div class="topbar">

        <div class="topbar-title">

            <h1>
                Dashboard
            </h1>

            <p>
                Overview of your account and system.
            </p>

        </div>


        <div class="user-mini">

            <div class="mini-avatar">
                <?= $initial ?>
            </div>


            <div class="mini-info">

                <strong>
                    <?= $name ?>
                </strong>

                <span>
                    <?= $role ?>
                </span>

            </div>

        </div>

    </div>


    <!-- WELCOME -->

    <section class="welcome">

        <div class="welcome-content">

            <div class="welcome-label">
                SoftgeniousDev Control Center
            </div>

            <h2>
                Welcome back, <?= $name ?>
            </h2>

            <p>
                Manage your account and access
                your available system tools.
            </p>

        </div>

    </section>


    <?php if ($isAdmin): ?>


        <!-- =================================
             STATISTICS
        ================================= -->

        <section class="stats">


            <div class="stat-card">

                <div class="stat-top">

                    <div class="stat-icon">
                        US
                    </div>

                    <div class="stat-line"></div>

                </div>

                <div class="stat-title">
                    User Management
                </div>

                <div class="stat-value">
                    Accounts
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <div class="stat-icon">
                        FL
                    </div>

                    <div class="stat-line"></div>

                </div>

                <div class="stat-title">
                    File Management
                </div>

                <div class="stat-value">
                    Documents
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <div class="stat-icon">
                        RP
                    </div>

                    <div class="stat-line"></div>

                </div>

                <div class="stat-title">
                    Reporting
                </div>

                <div class="stat-value">
                    PDF Reports
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <div class="stat-icon">
                        SC
                    </div>

                    <div class="stat-line"></div>

                </div>

                <div class="stat-title">
                    Security
                </div>

                <div class="stat-value">
                    Protected
                </div>

            </div>


        </section>


    <?php endif; ?>


    <!-- =================================
         MAIN CARDS
    ================================= -->

    <section class="content-grid">


        <!-- PROFILE -->

        <div class="card">

            <div class="card-header">

                <h3>
                    Account Overview
                </h3>

                <span>
                    PROFILE
                </span>

            </div>


            <div class="profile">

                <div class="profile-avatar">

                    <?= $initial ?>

                </div>


                <div class="profile-details">

                    <h4>
                        <?= $name ?>
                    </h4>

                    <p>
                        <?= $email ?>
                    </p>


                    <span class="role">
                        <?= $role ?>
                    </span>

                </div>

            </div>


            <p class="description">

                Your account is protected using secure
                authentication, password hashing, session
                management and role-based access control.

            </p>

        </div>


        <!-- QUICK ACTIONS -->

        <div class="card">

            <div class="card-header">

                <h3>
                    Quick Actions
                </h3>

                <span>
                    SHORTCUTS
                </span>

            </div>


            <div class="actions">


                <?php if ($isAdmin): ?>


                    <a
                        href="users.php"
                        class="action"
                    >

                        <span class="action-icon">
                            US
                        </span>

                        <span class="action-text">

                            <strong>
                                Manage Users
                            </strong>

                            <span>
                                View and edit accounts
                            </span>

                        </span>

                    </a>


                    <a
                        href="add_user.php"
                        class="action"
                    >

                        <span class="action-icon">
                            +
                        </span>

                        <span class="action-text">

                            <strong>
                                Add User
                            </strong>

                            <span>
                                Create an account
                            </span>

                        </span>

                    </a>


                    <a
                        href="upload_file.php"
                        class="action"
                    >

                        <span class="action-icon">
                            UP
                        </span>

                        <span class="action-text">

                            <strong>
                                Upload File
                            </strong>

                            <span>
                                Upload documents
                            </span>

                        </span>

                    </a>


                    <a
                        href="files.php"
                        class="action"
                    >

                        <span class="action-icon">
                            FL
                        </span>

                        <span class="action-text">

                            <strong>
                                View Files
                            </strong>

                            <span>
                                Manage documents
                            </span>

                        </span>

                    </a>


                <?php else: ?>


                    <a
                        href="logout.php"
                        class="action"
                    >

                        <span class="action-icon">
                            LO
                        </span>

                        <span class="action-text">

                            <strong>
                                Sign Out
                            </strong>

                            <span>
                                End your session
                            </span>

                        </span>

                    </a>


                <?php endif; ?>


            </div>

        </div>


        <?php if ($isAdmin): ?>


            <!-- SYSTEM FEATURES -->

            <div class="card">

                <div class="card-header">

                    <h3>
                        System Features
                    </h3>

                    <span>
                        SECURITY
                    </span>

                </div>


                <div class="features">


                    <div class="feature">

                        <div class="feature-icon">
                            SEC
                        </div>

                        <div class="feature-text">

                            <strong>
                                Secure Authentication
                            </strong>

                            <span>
                                Password hashing and session protection
                            </span>

                        </div>

                    </div>


                    <div class="feature">

                        <div class="feature-icon">
                            RBAC
                        </div>

                        <div class="feature-text">

                            <strong>
                                Role-Based Access
                            </strong>

                            <span>
                                Controlled administrator permissions
                            </span>

                        </div>

                    </div>


                    <div class="feature">

                        <div class="feature-icon">
                            API
                        </div>

                        <div class="feature-text">

                            <strong>
                                REST API
                            </strong>

                            <span>
                                Token-based JSON API access
                            </span>

                        </div>

                    </div>


                    <div class="feature">

                        <div class="feature-icon">
                            CSRF
                        </div>

                        <div class="feature-text">

                            <strong>
                                CSRF Protection
                            </strong>

                            <span>
                                Protected administrative forms
                            </span>

                        </div>

                    </div>


                </div>

            </div>


            <!-- REPORT -->

            <div class="card report-card">

                <div class="report-text">

                    <h3>
                        User Reports
                    </h3>

                    <p>
                        Generate a PDF report containing
                        registered user information.
                    </p>

                </div>


                <a
                    href="generate_users_pdf.php"
                    target="_blank"
                    class="report-btn"
                >
                    Generate Report
                </a>

            </div>


        <?php endif; ?>


    </section>


    <!-- FOOTER -->

    <footer class="footer">

        <strong>
            SoftgeniousDev
        </strong>

        &nbsp;•&nbsp;

        tech for learning, creating and growing

        <br><br>

        PHP Authentication & Administration System

    </footer>


</main>


</body>

</html>