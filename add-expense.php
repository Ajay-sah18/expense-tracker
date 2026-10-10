
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /PHP-Learning/expense-tracker/auth/login.php");
    exit;
}

require "config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $expense = trim($_POST["expense-title"] ?? "");
    $amount = $_POST["amount"] ?? "";
    $category = trim($_POST["category"] ?? "");
    $date = $_POST["date"] ?? "";

    $error = "";

    // Validate the amount
    if (!is_numeric($amount) || $amount <= 0) {
        $error = "Amount must be a number greater than zero.";
    }
    // Validate the expense title
    elseif ($expense === "") {
        $error = "Expense title cannot be empty.";
    }

    // Display error or submitted values
    if ($error !== "") {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    } else {
        echo "<p>Expense: " . htmlspecialchars($expense) . "</p>";
        echo "<p>Amount: " . htmlspecialchars($amount) . "</p>";
        echo "<p>Category: " . htmlspecialchars($category) . "</p>";
        echo "<p>Date: " . htmlspecialchars($date) . "</p>";

        $expenseStatement = $pdo->prepare (
            "INSERT INTO expenses (user_id, title, amount, category, expense_date)
            VALUES (:user_id, :title, :amount, :category, :expense_date)"
        );

        $expenseStatement->execute([
            "user_id" => $_SESSION["user_id"],
            "title" => $expense,
            "amount" => $amount,
            "category" => $category,
            "expense_date" => $date
        ]);
    }
}

?>



<!DOCTYPE html>
<html>
<head>
    <title>Add Expense</title>
</head>
<body>

<form method="POST">
    <label for="expense-title">Expense Title</label>
    <input type="text" id="expense-title" name="expense-title">

    <label for="amount">Amount</label>
    <input type="number" id="amount" name="amount" step="0.01">

    <label for="category">Category</label>
    <input type="text" id="category" name="category">

    <label for="date">Expense Date</label>
    <input type="date" id="date" name="date">

    <button type="submit">Add Expense</button>
</form>
</body>
</html>