
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$timeout = 15 * 60; // 15 minutes in seconds

// Check whether the user is logged in.
if (!isset($_SESSION["user_id"])) {
    header("Location: /PHP-Learning/expense-tracker/auth/login.php");
    exit;
}

// Check whether the session has been inactive too long.
if (
    isset($_SESSION["last_activity"]) &&
    (time() - $_SESSION["last_activity"]) >= $timeout
) {
    // Clear session data.
    $_SESSION = [];

    // Remove the session cookie.
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

    // Destroy the expired session.
    session_destroy();

    header(
        "Location: /PHP-Learning/expense-tracker/auth/login.php?expired=1"
    );
    exit;
}

// Record the current activity time.
$_SESSION["last_activity"] = time();
