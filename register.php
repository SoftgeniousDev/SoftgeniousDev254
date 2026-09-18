<?php

session_start();

require_once 'config/database.php';
require_once 'includes/csrf.php';

$message = '';
$messageType = '';

$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrfToken();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {

        $message = 'All fields are required.';
        $messageType = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = 'Please enter a valid email address.';
        $messageType = 'error';

    } elseif (strlen($password) < 6) {

        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';

    } elseif ($password !== $confirmPassword) {

        $message = 'Passwords do not match.';
        $messageType = 'error';

    } else {

        // Check whether the email already exists
        $stmt = $pdo->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $message = 'An account with that email already exists.';
            $messageType = 'error';

        } else {

            // Securely hash the password
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // New accounts are created as regular users
            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, 'user')"
            );

            $stmt->execute([
                $name,
                $email,
                $hashedPassword
            ]);

            $message = 'Registration successful! You can now sign in.';
            $messageType = 'success';

            $name = '';
            $email = '';
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

    <title>Create Account | SoftgeniousDev</title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

    <style>

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                linear-gradient(
                    135deg,
                    #0f172a,
                    #1e293b,
                    #2563eb
                );
            padding: 30px 15px;
            font-family: Arial, sans-serif;
        }

        .register-wrapper {
            width: 100%;
            max-width: 460px;
        }

        /* BRAND */

        .brand {
            text-align: center;
            color: white;
            margin-bottom: 22px;
        }

        .brand-name {
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .brand-name span {
            color: #60a5fa;
        }

        .brand-tagline {
            margin-top: 7px;
            color: #cbd5e1;
            font-size: 14px;
        }

        /* CARD */

        .register-card {
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow:
                0 20px 50px rgba(0, 0, 0, 0.25);
        }

        .register-card h1 {
            margin: 0;
            text-align: center;
            font-size: 28px;
            color: #111827;
        }

        .subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin: 8px 0 28px;
        }

        /* FORM */

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        .form-group input {
            width: 100%;
            padding: 13px 14px;
            margin: 0;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: 0.2s;
        }

        .form-group input:focus {
            border-color: #2563eb;
            box-shadow:
                0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        /* PASSWORD */

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 65px;
        }

        .show-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #2563eb;
            font-size: 13px;
            cursor: pointer;
            padding: 5px;
        }

        .password-wrapper .show-password:hover {
            background: transparent;
        }

        /* PASSWORD STRENGTH */

        .strength {
            width: 100%;
            height: 5px;
            background: #e5e7eb;
            border-radius: 10px;
            margin-top: 8px;
            overflow: hidden;
        }

        .strength-bar {
            width: 0;
            height: 100%;
            transition: 0.3s;
        }

        .password-hint {
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
        }

        /* BUTTON */

        .register-btn {
            width: 100%;
            padding: 13px;
            margin-top: 5px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .register-btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        /* MESSAGES */

        .message {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .message.success {
            background: #dcfce7;
            color: #166534;
        }

        /* LOGIN LINK */

        .login-link {
            text-align: center;
            margin-top: 22px;
            color: #6b7280;
            font-size: 14px;
        }

        .login-link a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* FOOTER */

        .footer {
            text-align: center;
            color: #cbd5e1;
            font-size: 12px;
            margin-top: 20px;
        }

        /* MOBILE */

        @media (max-width: 500px) {

            .register-card {
                padding: 25px 20px;
            }

            .brand-name {
                font-size: 26px;
            }

        }

    </style>

</head>

<body>

<div class="register-wrapper">

    <!-- BRAND -->

    <div class="brand">

        <div class="brand-name">
            Softgenious<span>Dev</span>
        </div>

        <div class="brand-tagline">
            tech for learning, creating and growing
        </div>

    </div>


    <!-- REGISTER CARD -->

    <div class="register-card">

        <h1>Create Account</h1>

        <p class="subtitle">
            Join SoftgeniousDev and get started
        </p>


        <?php if ($message !== ''): ?>

            <div class="message <?= htmlspecialchars($messageType) ?>">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <?= csrfField() ?>


            <!-- NAME -->

            <div class="form-group">

                <label for="name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter your full name"
                    value="<?= htmlspecialchars($name) ?>"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email address"
                    value="<?= htmlspecialchars($email) ?>"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a password"
                        minlength="6"
                        required
                    >

                    <button
                        type="button"
                        class="show-password"
                        onclick="togglePassword('password', this)"
                    >
                        Show
                    </button>

                </div>

                <div class="strength">
                    <div
                        id="strengthBar"
                        class="strength-bar"
                    ></div>
                </div>

                <div class="password-hint">
                    Password must contain at least 6 characters.
                </div>

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="form-group">

                <label for="confirm_password">
                    Confirm Password
                </label>

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        minlength="6"
                        required
                    >

                    <button
                        type="button"
                        class="show-password"
                        onclick="togglePassword(
                            'confirm_password',
                            this
                        )"
                    >
                        Show
                    </button>

                </div>

            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                class="register-btn"
            >
                Create Account
            </button>

        </form>


        <!-- LOGIN -->

        <div class="login-link">

            Already have an account?

            <a href="login.php">
                Sign in
            </a>

        </div>

    </div>


    <div class="footer">

        © <?= date('Y') ?> SoftgeniousDev

    </div>

</div>


<script>

function togglePassword(id, button) {

    const input = document.getElementById(id);

    if (input.type === 'password') {

        input.type = 'text';
        button.textContent = 'Hide';

    } else {

        input.type = 'password';
        button.textContent = 'Show';

    }

}


const password = document.getElementById('password');
const strengthBar = document.getElementById('strengthBar');

password.addEventListener('input', function () {

    const value = password.value;

    let strength = 0;

    if (value.length >= 6) {
        strength++;
    }

    if (value.length >= 10) {
        strength++;
    }

    if (/[A-Z]/.test(value)) {
        strength++;
    }

    if (/[0-9]/.test(value)) {
        strength++;
    }

    if (/[^A-Za-z0-9]/.test(value)) {
        strength++;
    }


    if (strength === 0) {

        strengthBar.style.width = '0%';

    } else if (strength === 1) {

        strengthBar.style.width = '20%';

    } else if (strength === 2) {

        strengthBar.style.width = '40%';

    } else if (strength === 3) {

        strengthBar.style.width = '60%';

    } else if (strength === 4) {

        strengthBar.style.width = '80%';

    } else {

        strengthBar.style.width = '100%';

    }

});

</script>

</body>

</html>