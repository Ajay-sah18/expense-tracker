
<?php

session_start();

// Clear all session variables
$_SESSION = [];

// Remove the session cookie from the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(session_name(), "", [
        "expires" => time() - 42000,
        "path" => $params["path"],
        "domain" => $params["domain"],
        "secure" => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => $params["samesite"] ?? "Lax"
    ]);
}

// Destroy the session on the server
session_destroy();

// Redirect to login
header("Location: /PHP-Learning/expense-tracker/auth/login.php");
exit;
