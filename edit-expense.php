<?php

session_start();

//check whether the user is logged in.

if (!isset($_SESSION["user_id"])) {
    header("Location: /PHP-Learning/expense-tracker/auth/login.php");
    exit;
}

require "config/database.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

//Read the expense ID from the URL.
$expenseId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($expenseId === false || $expenseId === null || $expenseId <= 0){
    exit("Invalid expense ID.");
}

$submittedToken = $_POST["csrf_token"] ?? "";

if (
    !is_string($submittedToken) ||
    !is_string($_SESSION["csrf_token"] ?? null) ||
    !hash_equals($_SESSION["csrf_token"], $submittedToken)
) {
    http_response_code(403);
    exit("Invalid CSRF token. Request rejected.");
}

    $title = trim($_POST["title"] ?? "");
    $amount = trim($_POST["amount"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $expenseDate = trim($_POST["expense_date"] ?? "");

$formData = [
    "title" => $title,
    "amount" => $amount,
    "category" => $category,
    "expense_date" => $expenseDate
];
    $errors = [];

    if ($title === "") {
      $errors[] = "Expense title is required.";
    }

     if (!is_numeric($amount) || (float) $amount <= 0 ) {
        $errors[] =  "Amount must be  greater than zero.";
     }

     if ($category === "") {
         $errors[] = "Category is required.";
     }

     $dateObject = DateTime::createFromFormat("!Y-m-d", $expenseDate);

     if (
        !$dateObject ||
        $dateObject->format("Y-m-d") !== $expenseDate
     ) {
        $errors[] = "Please enter a valid date.";
     }
     if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars(
            $error,
            ENT_QUOTES,
            "UTF-8"
        ) . "</p>";
    }
} else {
    $checkStatement = $pdo->prepare(
    "SELECT id FROM expenses
     WHERE id = :id AND user_id = :user_id"
);

$checkStatement->execute([
    ":id" => $expenseId,
    ":user_id" => $_SESSION["user_id"]
]);

if (!$checkStatement->fetch(PDO::FETCH_ASSOC)) {
    http_response_code(404);
    exit("Expense not found.");
}
  $updateStatement = $pdo->prepare(
    "UPDATE expenses
    SET title = :title,
        amount = :amount,
        category = :category,
        expense_date = :expense_date
        WHERE id = :id AND user_id = :user_id"
  );

  $updateStatement->execute([
    ":title" => $title,
    ":amount" => $amount,
    ":category" => $category,
    ":expense_date" => $expenseDate,
    ":id" => $expenseId,
    ":user_id" => $_SESSION["user_id"]
  ]);
  $_SESSION["success_message"] = "Expense updated successfully!";

header("Location: /PHP-Learning/expense-tracker/view-expenses.php");
exit;
}



//Find the expense belonging to the logged-in user.

$expenseStatement = $pdo->prepare(
    "SELECT * FROM expenses
    WHERE id = :id AND user_id = :user_id"
);

$expenseStatement->execute([
    ":id" => $expenseId,
    ":user_id" => $_SESSION["user_id"]
]);

$expense = $expenseStatement->fetch(PDO::FETCH_ASSOC);

//Stop if the expense doesn't exist or belongs to another user.

if(!$expense) {
    http_response_code(404);
    exit("Expense not found.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $formData = [
        "title" => $expense["title"],
        "amount" => $expense["amount"],
        "category" => $expense["category"],
        "expense_date" => $expense["expense_date"]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Edit Expense</title>
</head>
<body>

 <h1>Edit Expense</h1>

    <form method="POST" action="">

    <input type="hidden" name="csrf_token"
    value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") ?>"
    >

        <label for="title">Expense Title:</label>
        <input
            type="text"
            id="title"
            name="title"
            value="<?= htmlspecialchars($formData["title"], ENT_QUOTES, "UTF-8") ?>"
            required
        >
        <br><br>

        <label for="amount">Amount:</label>
        <input
            type="number"
            id="amount"
            name="amount"
            step="0.01"
            min="0.01"
            value="<?= htmlspecialchars($formData["amount"], ENT_QUOTES, "UTF-8") ?>"
            required
        >
        <br><br>

        <label for="category">Category:</label>
        <input
            type="text"
            id="category"
            name="category"
            value="<?= htmlspecialchars($formData["category"], ENT_QUOTES, "UTF-8") ?>"
            required
        >
        <br><br>

        <label for="expense_date">Date:</label>
        <input
            type="date"
            id="expense_date"
            name="expense_date"
            value="<?= htmlspecialchars($formData["expense_date"], ENT_QUOTES, "UTF-8") ?>"
            required
        >
        <br><br>

        <button type="submit">Update Expense</button>

    </form>

    <p>
        <a href="/PHP-Learning/expense-tracker/view-expenses.php">
            Back to Expenses
        </a>
    </p>
</body>
</html>