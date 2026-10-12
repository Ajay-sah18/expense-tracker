
<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /PHP-Learning/expense-tracker/auth/login.php");
    exit;
}

require __DIR__ ."/database.php";

$totalStatement = $pdo->prepare(
    "SELECT SUM(amount) FROM expenses
    WHERE user_id = :user_id"
);

$totalStatement->execute([
    ":user_id" => $_SESSION["user_id"]
]);

$monthlyStatement = $pdo->prepare(
    "SELECT SUM(amount) FROM expenses
    WHERE user_id = :user_id
    AND MONTH(expense_date) = MONTH(CURDATE())
    AND YEAR(expense_date) = YEAR(CURDATE())"
);

$monthlyStatement->execute([
    ":user_id" => $_SESSION["user_id"]
]);

$countStatement = $pdo->prepare(
    "SELECT COUNT(*) FROM expenses
    WHERE user_id = :user_id"
);

$countStatement->execute([
    ":user_id" => $_SESSION["user_id"]
]);

$categoryStatement = $pdo->prepare(
    "SELECT category, SUM(amount) AS total_amount
    FROM expenses
    WHERE user_id = :user_id
    GROUP BY category"
);

$categoryStatement->execute([
    "user_id" => $_SESSION["user_id"]
]);


$totalExpenses = $totalStatement->fetchColumn() ?? 0;

$monthlyExpenses = $monthlyStatement->fetchColumn() ?? 0;

$expenseCount = $countStatement->fetchColumn() ?? 0;

$categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);

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

    <h2>Total Expenses</h2>
    <p>Rs. <?= number_format((float) $totalExpenses, 2) ?></p>

    <h2>This Month's Expenses</h2>
    <p>Rs. <?= number_format((float) $monthlyExpenses, 2) ?></p>

    <h2>Number of Expenses</h2>
    <p><?= number_format((int) $expenseCount) ?> expenses</p>

    <h2>Category wise Expenses</h2>

    <?php foreach ($categories as $category): ?>
        <p>
            <?= htmlspecialchars($category["category"], ENT_QUOTES, "UTF-8") ?>:
            Rs. <?= number_format((float) $category["total_amount"],2) ?>
    </p>
    <?php endforeach; ?>

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
