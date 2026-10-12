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
    <title>Register - Expense Tracker</title>
</head>
<body>

    <h2>Create an Account</h2>

    <?php if ($message !== ""): ?>
        <p style="color: <?= $messageType === 'success' ? 'green' : 'red' ?>;">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Enter your name</label>
        <input type="text" id="name" name="name" required>

        <br><br>

        <label for="email">Enter your email</label>
        <input type="email" id="email" name="email" required>

        <br><br>

        <label for="password">Enter password</label>
        <input type="password" id="password" name="password" required>

        <br><br>

        <button type="submit">Register</button>
    </form>

</body>
</html>