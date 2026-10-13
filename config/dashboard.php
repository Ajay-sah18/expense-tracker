
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
    <link rel="stylesheet" href="/PHP-Learning/expense-tracker/assets/css/style.css">
</head>

<body>
    <div class="app-layout">

        <aside class="sidebar">
            <h2 class="brand">Expense Tracker</h2>

            <div>
                <p class="sidebar-label">MAIN MENU</p>

                <nav class="nav-links">
                    <a href="/PHP-Learning/expense-tracker/config/dashboard.php"
                       class="nav-link active">
                        Dashboard
                    </a>

                    <a href="/PHP-Learning/expense-tracker/add-expense.php"
                       class="nav-link">
                        Add Expense
                    </a>

                    <a href="/PHP-Learning/expense-tracker/view-expenses.php"
                       class="nav-link">
                        View Expenses
                    </a>
                </nav>
            </div>

            <div class="sidebar-footer">
                <a href="/PHP-Learning/expense-tracker/logout.php"
                   class="nav-link">
                    Logout
                </a>
            </div>
        </aside>

        <main class="main-content">

            <header class="page-header">
                <div>
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-subtitle">
                        Welcome back,
                        <?= htmlspecialchars($_SESSION["user_name"], ENT_QUOTES, "UTF-8") ?>!
                    </p>
                </div>
                <a href="/PHP-Learning/expense-tracker/add-expense.php"
   class="button">
    + Add Expense
</a>
            </header>

<section class="stats-grid">

    <article class="stat-card">
        <p class="stat-label">Total Expenses</p>
        <p class="stat-value">
            Rs. <?= number_format((float) $totalExpenses, 2) ?>
        </p>
    </article>

    <article class="stat-card">
        <p class="stat-label">This Month's Expenses</p>
        <p class="stat-value">
            Rs. <?= number_format((float) $monthlyExpenses, 2) ?>
        </p>
    </article>

    <article class="stat-card">
        <p class="stat-label">Number of Expenses</p>
        <p class="stat-value">
            <?= number_format((int) $expenseCount) ?>
        </p>
    </article>

</section>


<section class="content-grid">

    <article class="panel">
        <h2 class="panel-title">Category-wise Expenses</h2>

        <div class="category-list">
            <?php if (empty($categories)): ?>
                <p class="empty-state">No expenses recorded yet.</p>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <div class="category-row">
                        <span class="category-name">
                            <?= htmlspecialchars(
                                $category["category"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </span>

                        <span class="category-amount">
                            Rs. <?= number_format(
                                (float) $category["total_amount"],
                                2
                            ) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </article>

<article class="panel">
    <h2 class="panel-title">Monthly Expenses</h2>

    <div class="chart-container">
        <canvas id="monthlyExpenseChart"></canvas>
    </div>
</article>


</section>


        </main>

    </div>
</body>

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
