
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
    <title>Add Expense</title>
</head>
<body>

<h1>Add Expense</h1>

<?php if ($error !== ""): ?>
    <p>
        <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
    </p>
<?php endif; ?>

<form method="POST">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") ?>"
    >

    <label for="expense-title">Expense Title</label>
    <input type="text" id="expense-title"
           name="expense-title" required>

    <label for="amount">Amount</label>
    <input type="number" id="amount" name="amount"
           step="0.01" min="0.01" required>

    <label for="category">Category</label>
    <input type="text" id="category" name="category" required>

    <label for="date">Expense Date</label>
    <input type="date" id="date" name="date" required>

    <button type="submit">Add Expense</button>
</form>

<p>
    <a href="/PHP-Learning/expense-tracker/config/dashboard.php">
        Back to Dashboard
    </a>
</p>

</body>
</html>
