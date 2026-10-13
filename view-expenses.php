<?php

require __DIR__ . "/config/auth.php";
require __DIR__ . "/config/database.php";

if(empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

//Process the delete form.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  if (
    !isset($_POST["csrf_token"], $_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
  ) {
    http_response_code(403);
    exit("Invalid CSRF token. Request rejected.");
  }

    $expenseId = filter_input(
        INPUT_POST,
        "expense_id",
        FILTER_VALIDATE_INT
    );

    if ($expenseId !== false && $expenseId !== null && $expenseId > 0) {

        // Create the prepared statement first.
        $deleteStatement = $pdo->prepare(
            "DELETE FROM expenses
             WHERE id = :id AND user_id = :user_id"
        );

        // Then execute it.
        $deleteStatement->execute([
            ":id" => $expenseId,
            ":user_id" => $_SESSION["user_id"]
        ]);
    }

    header("Location: /PHP-Learning/expense-tracker/view-expenses.php");
    exit;
}

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");
$fromDate = trim($_GET["from_date"]?? "");
$toDate = trim($_GET["to_date"] ?? "");
$sort = $_GET["sort"] ?? "date_desc";

$allowedSorts = [
    "date_desc" => "expense_date DESC",
    "date_asc" => "expense_date ASC",
    "amount_desc" => "amount DESC",
    "amount_asc" => "amount ASC",
    "title_asc" => "title ASC",
    "title_desc" => "title DESC"
];

if (!array_key_exists($sort, $allowedSorts)) {
    $sort = "date_desc";
}

$limit = 10;

$page = filter_input(
    INPUT_GET,
    "page",
    FILTER_VALIDATE_INT
);

if ($page === false || $page === null || $page < 1){
    $page = 1;
}

$offset = ($page - 1) * $limit;

$dateErrors = [];

if ($fromDate !== "") {
    $dateObject = DateTime::CreateFromFormat("!Y-m-d", $fromDate);

    if (
        !$dateObject ||
        $dateObject->format("Y-m-d") !== $fromDate
    ) {
        $dateErrors[] = "Please enter a valid From date.";
    }
}

if ($toDate !== ""){
    $dateObject = DateTime::CreateFromFormat("!Y-m-d", $toDate);

    if (
        !$dateObject ||
        $dateObject->format("Y-m-d") !== $toDate
    ) {
    $dateErrors[] = "Please enter a valid To date.";

    }
}

if (
    $fromDate !== "" &&
    $toDate !== "" &&
    empty($dateErrors) &&
    $fromDate > $toDate
) {

    $dateErrors[] = "From date cannot be later than To date.";
}

if (empty($dateErrors)) {

$sql = "SELECT * FROM expenses WHERE user_id = :user_id";
$params = [
    ":user_id" => $_SESSION["user_id"]
];

if($search !== "") {
    $sql .= " AND title LIKE :search";
    $params[":search"] = "%" . $search . "%";
}

if ($category !== "") {
    $sql .= " AND category = :category";
    $params[":category"] = $category;
}

if ($fromDate !== "") {
    $sql .= " AND expense_date >= :from_date";
    $params[":from_date"] = $fromDate;
}
if ($toDate !== "") {
    $sql .= " AND expense_date <= :to_date";
    $params[":to_date"] = $toDate;
}

$sql .= " ORDER BY " . $allowedSorts[$sort];

$countSql = str_replace(
    "SELECT *",
    "SELECT COUNT(*)",
    $sql
);

$countStatement = $pdo->prepare($countSql);
$countStatement->execute($params);

$totalExpenses = $countStatement->fetchColumn();
$totalPages = (int) ceil($totalExpenses / $limit);

$sql .= " LIMIT :limit OFFSET :offset";
$params[":limit"] = $limit;
$params[":offset"] = $offset;

$expenseStatement = $pdo->prepare($sql);
$expenseStatement->bindValue(
    ":user_id",
    $_SESSION["user_id"],
    PDO::PARAM_INT
);
// Bind pagination values as integers
$expenseStatement->bindValue(":limit", $limit, PDO::PARAM_INT);
$expenseStatement->bindValue(":offset", $offset, PDO::PARAM_INT);

// Bind any remaining filter parameters
foreach ($params as $key => $value) {
    if ($key !== ":user_id" && $key !== ":limit" && $key !== ":offset") {
        $expenseStatement->bindValue($key, $value);
    }
}

$expenseStatement->execute();
$expenses = $expenseStatement->fetchAll(PDO::FETCH_ASSOC);
} else {
    $expenses = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Expenses</title>
    <link rel="stylesheet" href="/PHP-Learning/expense-tracker/assets/css/style.css">
</head>

<body>

<div class="app-layout">
    <aside class="sidebar">
        <h2 class="brand">Expense Tracker</h2>

        <nav>
            <p class="sidebar-label">MAIN MENU</p>

            <div class="nav-links">
                <a href="/PHP-Learning/expense-tracker/dashboard.php" class="nav-link">
                    Dashboard
                </a>

                <a href="/PHP-Learning/expense-tracker/add-expense.php" class="nav-link">
                    Add Expense
                </a>

                <a href="/PHP-Learning/expense-tracker/view-expenses.php" class="nav-link active">
                    View Expenses
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <a href="/PHP-Learning/expense-tracker/logout.php" class="nav-link">
                Logout
            </a>
        </div>
    </aside>

    <main class="main-content">

<div class="page-header">
    <div class="page-heading">
        <h1 class="page-title">My Expenses</h1>
        <p class="page-subtitle">Search, filter, and manage your expenses.</p>
    </div>

    <a href="/PHP-Learning/expense-tracker/export.php"
       class="button button-secondary">
        Export Expenses (CSV)
    </a>
</div>



         <?php if (!empty($dateErrors)): ?>
    <div style="color: red; margin-bottom: 15px;">
        <?php foreach ($dateErrors as $error): ?>
            <p><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<section class="panel filter-panel">
<form method="GET" action="" class="search-form">

    <div class="form-group">
        <label for="search">Search by title:</label>
        <input
            type="text"
            id="search"
            name="search"
            class="form-control"
            placeholder="Enter expense title"
            value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
        >
    </div>

    <div class="form-group">
        <label for="category">Category:</label>
        <select id="category" name="category" class="form-control">
           <option value="">All Categories</option>
<option value="Food" <?= $category === "Food" ? "selected" : "" ?>>Food</option>
<option value="Transport" <?= $category === "Transport" ? "selected" : "" ?>>Transport</option>
<option value="Shopping" <?= $category === "Shopping" ? "selected" : "" ?>>Shopping</option>
<option value="Bills" <?= $category === "Bills" ? "selected" : "" ?>>Bills and Utilities</option>
<option value="Education" <?= $category === "Education" ? "selected" : "" ?>>Education</option>
<option value="Health" <?= $category === "Health" ? "selected" : "" ?>>Health</option>
<option value="Entertainment" <?= $category === "Entertainment" ? "selected" : "" ?>>Entertainment</option>
<option value="Other" <?= $category === "Other" ? "selected" : "" ?>>Other</option>
        </select>
    </div>

    <div class="form-group">
        <label for="from_date">From Date:</label>
        <input
            type="date"
            id="from_date"
            name="from_date"
            class="form-control"
            value="<?= htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8') ?>"
        >
    </div>

    <div class="form-group">
        <label for="to_date">To Date:</label>
        <input
            type="date"
            id="to_date"
            name="to_date"
            class="form-control"
            value="<?= htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8') ?>"
        >
    </div>

    <div class="form-group">
        <label for="sort">Sort by:</label>
        <select name="sort" id="sort" class="form-control">
            <option value="date_desc" <?= $sort === "date_desc" ? "selected" : "" ?>>
                Newest first
            </option>
            <option value="date_asc" <?= $sort === "date_asc" ? "selected" : "" ?>>
                Oldest first
            </option>
            <option value="amount_desc" <?= $sort === "amount_desc" ? "selected" : "" ?>>
                Highest amount
            </option>
            <option value="amount_asc" <?= $sort === "amount_asc" ? "selected" : "" ?>>
                Lowest amount
            </option>
            <option value="title_asc" <?= $sort === "title_asc" ? "selected" : "" ?>>
                Title A-Z
            </option>
            <option value="title_desc" <?= $sort === "title_desc" ? "selected" : "" ?>>
                Title Z-A
            </option>
        </select>
    </div>

    <button type="submit" class="button">Search</button>

</form>
        </section>


<div class="panel">
    <h2 class="panel-title">Expense History</h2>

    <div class="table-container">
        <table class="expense-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            No expenses found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($expenses as $expense): ?>
                        <tr>
                            <td><?= htmlspecialchars($expense["title"], ENT_QUOTES, "UTF-8") ?></td>
                            <td><?= htmlspecialchars($expense["amount"], ENT_QUOTES, "UTF-8") ?></td>
                            <td><?= htmlspecialchars($expense["category"], ENT_QUOTES, "UTF-8") ?></td>
                            <td><?= htmlspecialchars($expense["expense_date"], ENT_QUOTES, "UTF-8") ?></td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        class="button button-secondary"
                                        href="/PHP-Learning/expense-tracker/edit-expense.php?id=<?= (int) $expense['id'] ?>"
                                    >Edit</a>

                                    <form method="POST" action="" class="delete-form">
                                        <input
                                            type="hidden"
                                            name="expense_id"
                                            value="<?= (int) $expense["id"] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") ?>"
                                        >

                                        <button type="submit" class="button button-danger">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php if($totalPages > 1): ?>
    <div class="pagination">

    <?php if($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ["page" => $page -1])) ?>">
         Previous
    </a>
    <?php endif; ?>

    <span>
        Page <?= $page ?> of <?= $totalPages ?>
    </span>

    <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ["page" => $page + 1])) ?>">
            Next
    </a>
    <?php endif; ?>
    </div>
    <?php endif; ?>
<p>
    <a href="/PHP-Learning/expense-tracker/config/dashboard.php">
        Back to Dashboard
</a>
</p>


<?php if (!empty($_SESSION["success_message"])): ?>
    <div id="success-message" class="alert-success">
        <?= htmlspecialchars(
            $_SESSION["success_message"],
            ENT_QUOTES,
            "UTF-8"
        ) ?>
    </div>
    <?php unset($_SESSION["success_message"]); ?>
<?php endif; ?>


<script>
    const message = document.getElementById("success-message");

    if (message) {
        setTimeout(() => {
            message.remove();
        }, 3000);
    }
</script>
</main>
</div>
</body>
</html>