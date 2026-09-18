<?php

session_start();

require_once 'config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $message = 'Email and password are required.';

    } else {

        $stmt = $pdo->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (
            $user &&
            password_verify($password, $user['password'])
        ) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header('Location: dashboard.php');
            exit;

        } else {

            $message = 'Invalid email or password.';
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

    <title>Login | SoftgeniousDev</title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

</head>

<body class="login-page">

    <div class="login-container">

        <div class="login-card">

            <!-- Brand -->

            <div class="login-brand">

                <h1>SoftgeniousDev</h1>

                <p>
                    Tech for learning, creating and growing
                </p>

            </div>


            <!-- Login heading -->

            <div class="login-title">

                <h2>Welcome back</h2>

                <p>
                    Sign in to access your account
                </p>

            </div>


            <!-- Error message -->

            <?php if ($message !== ''): ?>

                <div class="login-alert">

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <!-- Login form -->

            <form method="POST">

                <div class="login-field">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        autocomplete="email"
                        required
                    >

                </div>


                <div class="login-field">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="login-submit"
                >
                    Sign In
                </button>

            </form>


            <!-- Register -->

            <div class="login-register">

                <span>
                    Don't have an account?
                </span>

                <a href="register.php">
                    Create an account
                </a>

            </div>


            <!-- Security -->

            <div class="login-security">

                🔒 Secure authentication

            </div>

        </div>

    </div>

</body>

</html>