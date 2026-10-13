<?php
session_start();

require "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare(
        "SELECT id, name, email, password
        FROM users WHERE email = :email"
    );

    $stmt->execute([
        "email" => $email
    ]);

   $user = $stmt->fetch(PDO::FETCH_ASSOC);

   if ($user && password_verify($password, $user["password"])) {
    session_regenerate_id(true);

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["user_name"] = $user["name"];

     header("Location: /PHP-Learning/expense-tracker/config/dashboard.php");
     exit;

        } else {

        $message = "Invalid email or password.";
          }
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Expense Tracker</title>

    <link rel="stylesheet" href="/PHP-Learning/expense-tracker/assets/css/style.css">
</head>
<body class="auth-page">

    <main class="auth-container">

        <section class="auth-card">

            <div class="auth-heading">
                <h1 class="auth-title">Welcome Back!</h1>
                <p class="auth-subtitle">
                    Sign in to manage your expenses.
                </p>
            </div>

            <?php if ($message !== ""): ?>
                <div class="auth-message">
                    <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">

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
            placeholder="Enter your password"
            autocomplete="current-password"
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
                    Login
                </button>

            </form>

            <p class="auth-footer">
                Don't have an account?
                <a href="/PHP-Learning/expense-tracker/auth/register.php">
                    Register here
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
