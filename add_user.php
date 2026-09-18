<?php

require_once 'config/error.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';

requireAdmin();

$message = '';

$name = '';
$email = '';
$role = 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrfToken();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if ($name === '' || $email === '' || $password === '') {

        $message = 'All fields are required.';

    } elseif (strlen($name) < 2) {

        $message = 'Please enter a valid full name.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = 'Invalid email address.';

    } elseif (strlen($password) < 6) {

        $message = 'Password must be at least 6 characters.';

    } elseif (!in_array($role, ['user', 'admin'], true)) {

        $message = 'Invalid role.';

    } else {

        $stmt = $pdo->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $message = 'Email already exists.';

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO users
                (name, email, password, role)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $email,
                $hashedPassword,
                $role
            ]);

            header('Location: users.php');
            exit;
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

    <title>Add User | SoftgeniousDev</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f5f7fb;

            color: #172033;
        }

        a {
            text-decoration: none;
        }


        /* ================================
           SIDEBAR
        ================================= */

        .sidebar {
            position: fixed;

            top: 0;
            left: 0;
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

            padding:
                5px 12px 30px;
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

            font-size: 13px;

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
            padding:
                0 12px;

            margin:
                10px 0;

            color: #64748b;

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 1.2px;
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

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.06
                );

            font-size: 9px;

            font-weight: 800;
        }


        .nav-link.active
        .nav-icon {
            background:
                rgba(
                    255,
                    255,
                    255,
                    0.15
                );
        }


        .sidebar-bottom {
            position: absolute;

            left: 16px;
            right: 16px;

            bottom: 20px;

            padding-top: 15px;

            border-top:
                1px solid #1f2937;
        }


        .logout {
            color: #f87171;
        }


        /* ================================
           MAIN
        ================================= */

        .main {
            margin-left: 245px;

            min-height: 100vh;

            padding:
                30px 35px 60px;
        }


        .topbar {
            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 25px;
        }


        .topbar h1 {
            margin: 0;

            color: #111827;

            font-size: 25px;
        }


        .topbar p {
            margin:
                6px 0 0;

            color: #64748b;

            font-size: 13px;
        }


        .back-button {
            padding:
                10px 15px;

            border:
                1px solid #dbe1ea;

            border-radius: 8px;

            background: white;

            color: #475569;

            font-size: 12px;

            font-weight: 700;

            transition: 0.2s ease;
        }


        .back-button:hover {
            background: #f8fafc;

            border-color: #cbd5e1;
        }


        /* ================================
           FORM CARD
        ================================= */

        .form-wrapper {
            width: 100%;

            max-width: 900px;

            margin: 0 auto;
        }


        .form-card {
            background: white;

            border:
                1px solid #e5e7eb;

            border-radius: 18px;

            overflow: hidden;

            box-shadow:
                0 10px 35px
                rgba(
                    15,
                    23,
                    42,
                    0.06
                );
        }


        /* ================================
           FORM HEADER
        ================================= */

        .form-header {
            padding:
                30px 35px;

            background:
                linear-gradient(
                    135deg,
                    #111827,
                    #1e3a8a
                );

            color: white;
        }


        .form-header h2 {
            margin: 0 0 7px;

            font-size: 21px;
        }


        .form-header p {
            margin: 0;

            color: #cbd5e1;

            font-size: 13px;
        }


        /* ================================
           PROGRESS
        ================================= */

        .progress-section {
            padding:
                25px 35px 5px;
        }


        .progress {
            display: flex;

            align-items: center;

            width: 100%;
        }


        .progress-step {
            display: flex;

            align-items: center;

            gap: 8px;

            color: #94a3b8;

            font-size: 11px;

            font-weight: 700;

            white-space: nowrap;
        }


        .circle {
            width: 32px;
            height: 32px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: #f1f5f9;

            color: #64748b;

            font-size: 11px;

            font-weight: 800;
        }


        .progress-step.active {
            color: #2563eb;
        }


        .progress-step.active
        .circle {
            background: #2563eb;

            color: white;

            box-shadow:
                0 0 0 4px
                rgba(
                    37,
                    99,
                    235,
                    0.1
                );
        }


        .progress-line {
            flex: 1;

            height: 2px;

            background: #e2e8f0;

            margin:
                0 14px;
        }


        /* ================================
           ALERT
        ================================= */

        .alert {
            margin:
                20px 35px 0;

            padding:
                12px 15px;

            border-radius: 9px;

            font-size: 12px;

            font-weight: 600;
        }


        .alert-error {
            background: #fef2f2;

            border:
                1px solid #fecaca;

            color: #b91c1c;
        }


        /* ================================
           FORM BODY
        ================================= */

        .form-body {
            padding:
                30px 35px 35px;
        }


        .step {
            display: none;
        }


        .step.active {
            display: block;
        }


        .section-heading {
            margin-bottom: 25px;
        }


        .section-heading h3 {
            margin: 0 0 6px;

            color: #111827;

            font-size: 18px;
        }


        .section-heading p {
            margin: 0;

            color: #64748b;

            font-size: 12px;
        }


        .form-grid {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;
        }


        .form-group.full {
            grid-column:
                1 / -1;
        }


        label {
            display: block;

            margin-bottom: 7px;

            color: #334155;

            font-size: 12px;

            font-weight: 700;
        }


        .required {
            color: #ef4444;
        }


        input,
        select {
            width: 100%;

            height: 44px;

            padding:
                0 13px;

            border:
                1px solid #d7dee8;

            border-radius: 9px;

            background: white;

            color: #172033;

            font-size: 13px;

            outline: none;

            transition: 0.2s ease;
        }


        input::placeholder {
            color: #a1aab8;
        }


        input:focus,
        select:focus {
            border-color: #3b82f6;

            box-shadow:
                0 0 0 3px
                rgba(
                    59,
                    130,
                    246,
                    0.1
                );
        }


        .help-text {
            margin-top: 6px;

            color: #94a3b8;

            font-size: 10px;
        }


        /* ================================
           PASSWORD
        ================================= */

        .password-wrapper {
            position: relative;
        }


        .password-wrapper input {
            padding-right: 65px;
        }


        .password-toggle {
            position: absolute;

            top: 50%;
            right: 10px;

            transform:
                translateY(-50%);

            border: none;

            background: transparent;

            color: #64748b;

            font-size: 10px;

            font-weight: 800;

            cursor: pointer;
        }


        .password-toggle:hover {
            color: #2563eb;
        }


        /* ================================
           CONFIRMATION
        ================================= */

        .review-box {
            border:
                1px solid #e2e8f0;

            border-radius: 12px;

            overflow: hidden;
        }


        .review-row {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            padding:
                15px 17px;

            border-bottom:
                1px solid #e2e8f0;
        }


        .review-row:last-child {
            border-bottom: none;
        }


        .review-label {
            color: #64748b;

            font-size: 11px;

            font-weight: 600;
        }


        .review-value {
            color: #111827;

            font-size: 12px;

            font-weight: 700;

            text-align: right;
        }


        .role-badge {
            display: inline-block;

            padding:
                5px 10px;

            border-radius: 20px;

            background: #dbeafe;

            color: #1d4ed8;

            font-size: 9px;

            font-weight: 800;

            text-transform: uppercase;
        }


        /* ================================
           BUTTONS
        ================================= */

        .form-buttons {
            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-top: 30px;

            padding-top: 22px;

            border-top:
                1px solid #e5e7eb;
        }


        .button-group {
            display: flex;

            gap: 10px;
        }


        .btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            min-width: 105px;

            height: 42px;

            padding:
                0 17px;

            border: none;

            border-radius: 8px;

            font-size: 12px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s ease;
        }


        .btn-primary {
            background: #2563eb;

            color: white;
        }


        .btn-primary:hover {
            background: #1d4ed8;
        }


        .btn-success {
            background: #16a34a;

            color: white;
        }


        .btn-success:hover {
            background: #15803d;
        }


        .btn-secondary {
            background: #f1f5f9;

            border:
                1px solid #e2e8f0;

            color: #475569;
        }


        .btn-secondary:hover {
            background: #e2e8f0;
        }


        /* ================================
           FOOTER
        ================================= */

        .footer {
            text-align: center;

            margin-top: 30px;

            color: #94a3b8;

            font-size: 11px;
        }


        .footer strong {
            color: #64748b;
        }


        /* ================================
           RESPONSIVE
        ================================= */

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


            .menu-title {
                display: none;
            }


            .nav-menu {
                flex-direction: row;

                overflow-x: auto;
            }


            .nav-link {
                white-space: nowrap;
            }


            .sidebar-bottom {
                position: static;

                margin-top: 10px;

                border-top:
                    1px solid #1f2937;
            }


            .main {
                margin-left: 0;

                padding:
                    22px 18px 40px;
            }

        }


        @media (max-width: 600px) {

            .topbar {
                flex-direction: column;

                align-items: flex-start;

                gap: 15px;
            }


            .form-header {
                padding:
                    25px 20px;
            }


            .progress-section {
                padding:
                    20px 20px 5px;
            }


            .progress-step span {
                display: none;
            }


            .progress-line {
                margin:
                    0 8px;
            }


            .form-body {
                padding:
                    25px 20px;
            }


            .form-grid {
                grid-template-columns: 1fr;
            }


            .form-group.full {
                grid-column: auto;
            }


            .alert {
                margin:
                    20px 20px 0;
            }


            .form-buttons {
                flex-direction: column;

                gap: 10px;
            }


            .button-group {
                width: 100%;
            }


            .button-group .btn {
                flex: 1;
            }


            .form-buttons > .btn {
                width: 100%;
            }


            .review-row {
                align-items: flex-start;

                flex-direction: column;

                gap: 5px;
            }


            .review-value {
                text-align: left;
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
            class="nav-link"
        >

            <span class="nav-icon">
                DB
            </span>

            Dashboard

        </a>


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
            class="nav-link active"
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


    <div class="topbar">

        <div>

            <h1>
                Add User
            </h1>

            <p>
                Create a new account and assign system permissions.
            </p>

        </div>


        <a
            href="users.php"
            class="back-button"
        >
            ← Back to Users
        </a>

    </div>


    <div class="form-wrapper">


        <div class="form-card">


            <!-- HEADER -->

            <div class="form-header">

                <h2>
                    Create User Account
                </h2>

                <p>
                    Complete the steps below to register a new system user.
                </p>

            </div>


            <!-- PROGRESS -->

            <div class="progress-section">

                <div class="progress">


                    <div
                        class="progress-step active"
                        id="indicator1"
                    >

                        <div class="circle">
                            1
                        </div>

                        <span>
                            Personal
                        </span>

                    </div>


                    <div class="progress-line"></div>


                    <div
                        class="progress-step"
                        id="indicator2"
                    >

                        <div class="circle">
                            2
                        </div>

                        <span>
                            Account
                        </span>

                    </div>


                    <div class="progress-line"></div>


                    <div
                        class="progress-step"
                        id="indicator3"
                    >

                        <div class="circle">
                            3
                        </div>

                        <span>
                            Confirm
                        </span>

                    </div>


                </div>

            </div>


            <?php if ($message !== ''): ?>

                <div class="alert alert-error">

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                id="userForm"
            >

                <?= csrfField() ?>


                <!-- =================================
                     STEP 1
                ================================= -->

                <div
                    class="step active"
                    id="step1"
                >

                    <div class="form-body">


                        <div class="section-heading">

                            <h3>
                                Personal Information
                            </h3>

                            <p>
                                Enter the basic information for the new user.
                            </p>

                        </div>


                        <div class="form-grid">


                            <div class="form-group full">

                                <label for="name">

                                    Full Name

                                    <span class="required">
                                        *
                                    </span>

                                </label>


                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="<?= htmlspecialchars($name) ?>"
                                    placeholder="Enter full name"
                                    autocomplete="name"
                                    required
                                >


                                <div class="help-text">

                                    Use the user's official full name.

                                </div>

                            </div>


                        </div>


                        <div class="form-buttons">

                            <a
                                href="users.php"
                                class="btn btn-secondary"
                            >
                                Cancel
                            </a>


                            <button
                                type="button"
                                class="btn btn-primary"
                                onclick="goToStep(2)"
                            >
                                Continue →
                            </button>

                        </div>


                    </div>

                </div>


                <!-- =================================
                     STEP 2
                ================================= -->

                <div
                    class="step"
                    id="step2"
                >

                    <div class="form-body">


                        <div class="section-heading">

                            <h3>
                                Account Information
                            </h3>

                            <p>
                                Configure the user's login credentials and role.
                            </p>

                        </div>


                        <div class="form-grid">


                            <div class="form-group full">

                                <label for="email">

                                    Email Address

                                    <span class="required">
                                        *
                                    </span>

                                </label>


                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= htmlspecialchars($email) ?>"
                                    placeholder="user@example.com"
                                    autocomplete="email"
                                    required
                                >

                            </div>


                            <div class="form-group">

                                <label for="password">

                                    Password

                                    <span class="required">
                                        *
                                    </span>

                                </label>


                                <div class="password-wrapper">

                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        minlength="6"
                                        placeholder="Minimum 6 characters"
                                        autocomplete="new-password"
                                        required
                                    >


                                    <button
                                        type="button"
                                        class="password-toggle"
                                        onclick="togglePassword()"
                                        id="passwordToggle"
                                    >
                                        SHOW
                                    </button>

                                </div>


                                <div class="help-text">
                                    Password must contain at least 6 characters.
                                </div>

                            </div>


                            <div class="form-group">

                                <label for="role">

                                    Account Role

                                    <span class="required">
                                        *
                                    </span>

                                </label>


                                <select
                                    id="role"
                                    name="role"
                                    required
                                >

                                    <option
                                        value="user"
                                        <?= $role === 'user'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Standard User
                                    </option>


                                    <option
                                        value="admin"
                                        <?= $role === 'admin'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Administrator
                                    </option>

                                </select>


                                <div class="help-text">
                                    Administrators have access to management tools.
                                </div>

                            </div>


                        </div>


                        <div class="form-buttons">

                            <button
                                type="button"
                                class="btn btn-secondary"
                                onclick="goToStep(1)"
                            >
                                ← Back
                            </button>


                            <button
                                type="button"
                                class="btn btn-primary"
                                onclick="goToStep(3)"
                            >
                                Review Account →
                            </button>

                        </div>


                    </div>

                </div>


                <!-- =================================
                     STEP 3
                ================================= -->

                <div
                    class="step"
                    id="step3"
                >

                    <div class="form-body">


                        <div class="section-heading">

                            <h3>
                                Review Account
                            </h3>

                            <p>
                                Check the information before creating the account.
                            </p>

                        </div>


                        <div class="review-box">


                            <div class="review-row">

                                <span class="review-label">
                                    Full Name
                                </span>

                                <span
                                    class="review-value"
                                    id="confirmName"
                                >
                                </span>

                            </div>


                            <div class="review-row">

                                <span class="review-label">
                                    Email Address
                                </span>

                                <span
                                    class="review-value"
                                    id="confirmEmail"
                                >
                                </span>

                            </div>


                            <div class="review-row">

                                <span class="review-label">
                                    Password
                                </span>

                                <span class="review-value">
                                    Protected
                                </span>

                            </div>


                            <div class="review-row">

                                <span class="review-label">
                                    Account Role
                                </span>

                                <span
                                    class="review-value"
                                    id="confirmRole"
                                >
                                </span>

                            </div>


                        </div>


                        <div class="form-buttons">

                            <button
                                type="button"
                                class="btn btn-secondary"
                                onclick="goToStep(2)"
                            >
                                ← Edit Details
                            </button>


                            <button
                                type="submit"
                                class="btn btn-success"
                            >
                                Create User
                            </button>

                        </div>


                    </div>

                </div>


            </form>


        </div>


        <div class="footer">

            <strong>
                SoftgeniousDev
            </strong>

            &nbsp;•&nbsp;

            tech for learning, creating and growing

        </div>


    </div>


</main>


<script>


function goToStep(step) {

    /*
     * Validate Step 1
     */

    if (step === 2) {

        const name =
            document.getElementById('name');

        if (!name.checkValidity()) {

            name.reportValidity();

            return;
        }
    }


    /*
     * Validate Step 2
     */

    if (step === 3) {

        const email =
            document.getElementById('email');

        const password =
            document.getElementById('password');

        const role =
            document.getElementById('role');


        if (!email.checkValidity()) {

            email.reportValidity();

            return;
        }


        if (!password.checkValidity()) {

            password.reportValidity();

            return;
        }


        if (!role.checkValidity()) {

            role.reportValidity();

            return;
        }


        /*
         * Populate review section
         */

        document.getElementById(
            'confirmName'
        ).textContent =
            document.getElementById(
                'name'
            ).value;


        document.getElementById(
            'confirmEmail'
        ).textContent =
            document.getElementById(
                'email'
            ).value;


        const roleValue =
            document.getElementById(
                'role'
            ).value;


        document.getElementById(
            'confirmRole'
        ).textContent =
            roleValue === 'admin'
                ? 'Administrator'
                : 'Standard User';

    }


    /*
     * Hide all steps
     */

    document.querySelectorAll(
        '.step'
    ).forEach(function(element) {

        element.classList.remove(
            'active'
        );

    });


    /*
     * Remove active indicators
     */

    document.querySelectorAll(
        '.progress-step'
    ).forEach(function(element) {

        element.classList.remove(
            'active'
        );

    });


    /*
     * Show selected step
     */

    document.getElementById(
        'step' + step
    ).classList.add(
        'active'
    );


    /*
     * Activate selected indicator
     */

    document.getElementById(
        'indicator' + step
    ).classList.add(
        'active'
    );

}


/*
 * Password visibility
 */

function togglePassword() {

    const password =
        document.getElementById(
            'password'
        );

    const button =
        document.getElementById(
            'passwordToggle'
        );


    if (
        password.type === 'password'
    ) {

        password.type = 'text';

        button.textContent = 'HIDE';

    } else {

        password.type = 'password';

        button.textContent = 'SHOW';

    }

}

</script>


</body>

</html>