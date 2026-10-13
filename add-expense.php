
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /PHP-Learning/expense-tracker/auth/login.php");
    exit;
}

require "config/database.php";

// Generate a CSRF token for the form
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verify the CSRF token
    $submittedToken = $_POST["csrf_token"] ?? "";

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION["csrf_token"], $submittedToken)
    ) {
        $error = "Invalid form submission. Please try again.";
    } else {
        $expense = trim($_POST["expense-title"] ?? "");
        $amount = $_POST["amount"] ?? "";
        $category = trim($_POST["category"] ?? "");
        $date = $_POST["date"] ?? "";

        // Validate the title
        if ($expense === "") {
            $error = "Expense title cannot be empty.";
        }
        // Validate the amount
        elseif (
            !is_numeric($amount) ||
            !is_finite((float) $amount) ||
            (float) $amount <= 0
        ) {
            $error = "Amount must be greater than zero.";
        }
        // Validate the category
        elseif ($category === "") {
            $error = "Category cannot be empty.";
        }
        // Validate the date format
        else {
            $dateObject = DateTime::createFromFormat("!Y-m-d", $date);

            if (
                !$dateObject ||
                $dateObject->format("Y-m-d") !== $date
            ) {
                $error = "Please enter a valid expense date.";
            }
        }

$allowedCategories = [
    "Food",
    "Transport",
    "Shopping",
    "Bills",
    "Education",
    "Health",
    "Entertainment",
    "Other"
];

if (!in_array($category, $allowedCategories, true)) {
    $error = "Please select a valid expense category.";
}

        // Save the expense if validation succeeds
        if ($error === "") {
            $expenseStatement = $pdo->prepare(
                "INSERT INTO expenses
                (user_id, title, amount, category, expense_date)
                VALUES
                (:user_id, :title, :amount, :category, :expense_date)"
            );

            $expenseStatement->execute([
                "user_id" => $_SESSION["user_id"],
                "title" => $expense,
                "amount" => $amount,
                "category" => $category,
                "expense_date" => $date
            ]);

            // Prevent accidental duplicate submission on refresh
            header(
                "Location: /PHP-Learning/expense-tracker/view-expenses.php?added=1"
            );
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense</title>

    <link rel="stylesheet" href="/PHP-Learning/expense-tracker/assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <aside class="sidebar">
        <h2 class="brand">Expense Tracker</h2>

        <nav>
            <p class="sidebar-label">MAIN MENU</p>

            <div class="nav-links">
                <a href="/PHP-Learning/expense-tracker/config/dashboard.php"
                   class="nav-link">
                    Dashboard
                </a>

                <a href="/PHP-Learning/expense-tracker/add-expense.php"
                   class="nav-link active">
                    Add Expense
                </a>

                <a href="/PHP-Learning/expense-tracker/view-expenses.php"
                   class="nav-link">
                    View Expenses
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <a href="/PHP-Learning/expense-tracker/logout.php"
               class="nav-link">
                Logout
            </a>
        </div>
    </aside>

    <main class="main-content">

        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Add Expense</h1>
                <p class="page-subtitle">
                    Record a new expense and keep your spending organized.
                </p>
            </div>
        </div>

        <?php if ($error !== ""): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <section class="panel">
            <h2 class="panel-title">Expense Details</h2>

            <form method="POST" class="expense-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") ?>"
                >

                <div class="form-group">
                    <label for="expense-title">Expense Title</label>
                    <input
                        type="text"
                        id="expense-title"
                        name="expense-title"
                        class="form-control"
                        placeholder="e.g. Lunch, Bus fare"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input
                        type="number"
                        id="amount"
                        name="amount"
                        class="form-control"
                        step="0.01"
                        min="0.01"
                        placeholder="Enter amount"
                        required
                    >
                </div>


<div class="form-group">
    <label for="category">Category</label>

    <select
        id="category"
        name="category"
        class="form-control"
        required
    >
        <option value="" disabled selected>
            Select a category
        </option>
        <option value="Food">Food</option>
        <option value="Transport">Transport</option>
        <option value="Shopping">Shopping</option>
        <option value="Bills">Bills and Utilities</option>
        <option value="Education">Education</option>
        <option value="Health">Health</option>
        <option value="Entertainment">Entertainment</option>
        <option value="Other">Other</option>
    </select>
</div>


                <div class="form-group">
                    <label for="date">Expense Date</label>
                    <input
                        type="date"
                        id="date"
                        name="date"
                        class="form-control"
                        required
                    >
                </div>

                <div class="form-actions">
                    <button type="submit" class="button">
                        Add Expense
                    </button>

                    <a href="/PHP-Learning/expense-tracker/dashboard.php"
                       class="button button-secondary">
                        Cancel
                    </a>
                </div>

            </form>
        </section>

    </main>
</div>

</body>
</html>
