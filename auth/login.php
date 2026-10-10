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

    $message = "Login successful! Welcome, " . htmlspecialchars($user["name"], ENT_QUOTES, "UTF-8");

    } else {
        $message = "Invalid email or password.";
          }
}
?>

<!DOCTYPE html>
<html lang="en">
 <head>
    <title>Login Page</title>
</head>
<body>
    <form method="POST">
        <label for="email">Enter your Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Enter your Password</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Login</button>
</form>

</body>
</html>

<?php if($message !== ""): ?>
<p><?=$message ?></p>
<?php endif; ?>