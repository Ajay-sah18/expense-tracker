
<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /PHP-Learning/expense-tracker/auth/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Tracker Dashboard</title>
</head>
<body>

    <h1>Welcome to your Expense Tracker</h1>

    <p>
        Hello,
        <?= htmlspecialchars($_SESSION["user_name"], ENT_QUOTES, "UTF-8") ?>!
    </p>

    <p>Your user ID is: <?= (int) $_SESSION["user_id"] ?></p>

     <h2>Expense Management</h2>
    <p>
        <a href="/PHP-Learning/expense-tracker/add-expense.php">
            Add Expense
</a>
</p>

<p>
    <a href="/PHP-Learning/expense-tracker/view-expenses.php">
        View Expenses
</a>
</p>
<p><a href="/PHP-Learning/expense-tracker/logout.php">Logout</a></p>


</body>
</html>
