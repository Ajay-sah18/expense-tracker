<?php

require __DIR__ . "/config/auth.php";
require __DIR__ . "/config/database.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

//Read the expense ID from the URL.
$expenseId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($expenseId === false || $expenseId === null || $expenseId <= 0){
    exit("Invalid expense ID.");
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

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

    if (!is_numeric($amount) || (float) $amount <= 0) {
        $errors[] = "Amount must be greater than zero.";
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
    $errors[] = "Please select a valid expense category.";
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

session_write_close();

header("Location: /PHP-Learning/expense-tracker/view-expenses.php");
exit;

    }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Expense</title>

    <link rel="stylesheet" href="/PHP-Learning/expense-tracker/assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <aside class="sidebar">
        <h2 class="brand">Expense Tracker</h2>

        <nav>
            <p class="sidebar-label">MAIN MENU</p>

            <div class="nav-links">
                <a href="/PHP-Learning/expense-tracker/dashboard.php"
                   class="nav-link">Dashboard</a>

                <a href="/PHP-Learning/expense-tracker/add-expense.php"
                   class="nav-link">Add Expense</a>

                <a href="/PHP-Learning/expense-tracker/view-expenses.php"
                   class="nav-link active">View Expenses</a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <a href="/PHP-Learning/expense-tracker/logout.php"
               class="nav-link">Logout</a>
        </div>
    </aside>

    <main class="main-content">

        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Edit Expense</h1>
                <p class="page-subtitle">
                    Update your expense details below.
                </p>
            </div>
        </div>

        <section class="panel">

            <h2 class="panel-title">Expense Details</h2>

            <form method="POST" action="" class="expense-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") ?>"
                >

                <div class="form-group">
                    <label for="title">Expense Title</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        class="form-control"
                        value="<?= htmlspecialchars($formData["title"], ENT_QUOTES, "UTF-8") ?>"
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
                        value="<?= htmlspecialchars($formData["amount"], ENT_QUOTES, "UTF-8") ?>"
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
        <option value="" disabled>Select a category</option>

        <option value="Food"
            <?= $formData["category"] === "Food" ? "selected" : "" ?>>
            Food
        </option>

        <option value="Transport"
            <?= $formData["category"] === "Transport" ? "selected" : "" ?>>
            Transport
        </option>

        <option value="Shopping"
            <?= $formData["category"] === "Shopping" ? "selected" : "" ?>>
            Shopping
        </option>

        <option value="Bills"
            <?= $formData["category"] === "Bills" ? "selected" : "" ?>>
            Bills and Utilities
        </option>

        <option value="Education"
            <?= $formData["category"] === "Education" ? "selected" : "" ?>>
            Education
        </option>

        <option value="Health"
            <?= $formData["category"] === "Health" ? "selected" : "" ?>>
            Health
        </option>

        <option value="Entertainment"
            <?= $formData["category"] === "Entertainment" ? "selected" : "" ?>>
            Entertainment
        </option>

        <option value="Other"
            <?= $formData["category"] === "Other" ? "selected" : "" ?>>
            Other
        </option>
    </select>
</div>


                <div class="form-group">
                    <label for="expense_date">Expense Date</label>
                    <input
                        type="date"
                        id="expense_date"
                        name="expense_date"
                        class="form-control"
                        value="<?= htmlspecialchars($formData["expense_date"], ENT_QUOTES, "UTF-8") ?>"
                        required
                    >
                </div>

                <div class="form-actions">
                    <button type="submit" class="button">
                        Update Expense
                    </button>

                    <a href="/PHP-Learning/expense-tracker/view-expenses.php"
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
