
<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: /PHP-Learning/expense-tracker/auth/login.php");
exit;
