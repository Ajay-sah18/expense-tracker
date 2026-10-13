<?php
require "../config/database.php";

$message = "";
$messageType = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($name) || empty($email) || empty($password)) {
        $message = "All fields are required.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
        $messageType = "error";

    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $messageType = "error";

    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $statement = $pdo->prepare("
                INSERT INTO users (name, email, password)
                VALUES (:name, :email, :password)
            ");

            $statement->execute([
                "name" => $name,
                "email" => $email,
                "password" => $hashedPassword
            ]);

            $message = "Registration successful!";
            $messageType = "success";

            header("Location: /PHP-Learning/expense-tracker/auth/login.php");
            exit;

        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                $message = "This email is already registered. Please use another email.";
                $messageType = "error";
            } else {
                error_log($e->getMessage());
                $message = "Registration failed. Please try again later.";
                $messageType = "error";
            }
        }
    }


}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Expense Tracker</title>

    <link rel="stylesheet" href="/PHP-Learning/expense-tracker/assets/css/style.css">
</head>
<body class="auth-page">

    <main class="auth-container">
        <section class="auth-card">

            <div class="auth-heading">
                <h1 class="auth-title">Create an Account</h1>
                <p class="auth-subtitle">
                    Start tracking your expenses today.
                </p>
            </div>

            <?php if ($message !== ""): ?>
                <div class="auth-message <?= $messageType === 'success' ? 'auth-message-success' : '' ?>"
                     role="status">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        placeholder="Enter your name"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="password-input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Create a password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="password-toggle"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <svg class="password-icon icon-show"
                                 xmlns="http://www.w3.org/2000/svg"
                                 width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>

                            <svg class="password-icon icon-hide"
                                 xmlns="http://www.w3.org/2000/svg"
                                 width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" aria-hidden="true" hidden>
                                <path d="M3 3l18 18"/>
                                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                <path d="M9.9 5.1A11 11 0 0 1 12 4.9c6.4 0 10 7.1 10 7.1a13 13 0 0 1-3.2 3.9"/>
                                <path d="M6.6 6.6C3.8 8.5 2 12 2 12s3.6 7.1 10 7.1a11 11 0 0 0 4.2-.8"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="button auth-button">
                    Create Account
                </button>

            </form>

            <p class="auth-footer">
                Already have an account?
                <a href="/PHP-Learning/expense-tracker/auth/login.php">
                    Login here
                </a>
            </p>

        </section>
    </main>

    <script>
        const passwordInput = document.getElementById("password");
        const passwordToggle = document.getElementById("password-toggle");
        const showIcon = passwordToggle.querySelector(".icon-show");
        const hideIcon = passwordToggle.querySelector(".icon-hide");

        passwordToggle.addEventListener("click", function () {
            const isPasswordHidden = passwordInput.type === "password";

            passwordInput.type = isPasswordHidden ? "text" : "password";

            showIcon.hidden = isPasswordHidden;
            hideIcon.hidden = !isPasswordHidden;

            passwordToggle.setAttribute(
                "aria-label",
                isPasswordHidden ? "Hide password" : "Show password"
            );

            passwordToggle.setAttribute(
                "aria-pressed",
                String(isPasswordHidden)
            );
        });
    </script>

</body>
</html>