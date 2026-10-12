
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

$totalExpenses = $totalStatement->fetchColumn() ?? 0;

$monthlyStatement = $pdo->prepare(
    "SELECT SUM(amount) FROM expenses
    WHERE user_id = :user_id
    AND MONTH(expense_date) = MONTH(CURDATE())
    AND YEAR(expense_date) = YEAR(CURDATE())"
);

$monthlyStatement->execute([
    ":user_id" => $_SESSION["user_id"]
]);

$monthlyExpenses = $monthlyStatement->fetchColumn() ?? 0;

$countStatement = $pdo->prepare(
    "SELECT COUNT(*) FROM expenses
    WHERE user_id = :user_id"
);

$countStatement->execute([
    ":user_id" => $_SESSION["user_id"]
]);

$expenseCount = $countStatement->fetchColumn() ?? 0;

$categoryStatement = $pdo->prepare(
    "SELECT category, SUM(amount) AS total_amount
    FROM expenses
    WHERE user_id = :user_id
    GROUP BY category"
);

$categoryStatement->execute([
    ":user_id" => $_SESSION["user_id"]
]);

$categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);

$monthlyChartStatement = $pdo->prepare(
    "SELECT
         YEAR(expense_date) AS expense_year,
         MONTH(expense_date) AS expense_month,
         SUM(amount) AS monthly_total
    FROM expenses
    WHERE user_id = :user_id
    GROUP BY
          YEAR(expense_date),
          MONTH(expense_date)
    ORDER BY
         expense_year,
         expense_month"
);

$monthlyChartStatement->execute([
    ":user_id" => $_SESSION["user_id"]
]);

$monthlyChartData = $monthlyChartStatement->fetchAll(PDO::FETCH_ASSOC);

$chartData = json_encode(
    $monthlyChartData,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);

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

    <h2>Monthly Expenses Chart</h2>
    <canvas id="monthlyExpenseChart"></canvas>

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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>
    const monthlyChartData = <?= $chartData ?>;


const labels = monthlyChartData.map(item => {
    const date = new Date(
        Number(item.expense_year),
        Number(item.expense_month) - 1
    );

    return date.toLocaleDateString("en-US", {
        month: "short",
        year: "numeric"
    });
});


    const amounts = monthlyChartData.map(
        item => Number(item.monthly_total)
    );

    const ctx = document.getElementById("monthlyExpenseChart");

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: labels,
            datasets: [{
                label: "Monthly Expenses (Rs.)",
                data: amounts
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>
