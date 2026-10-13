<?php

session_start();

if(empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION["user_id"])) {
    header ("Location: auth/login.php");
    exit;
}

require "config/database.php";

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
<html>

<head>
    <title>My Expenses</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        h1 {
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }
        .search-form label {
            font-size: 14px;
        }

        .search-form input,
        .search-form select {
            padding: 8px;
            max-width: 100%;
            box-sizing: border-box;
        }
        .search-form input[type="text"] {
            width: 200px;
        }
        .search-form input[type="date"]{
            width: 160px;
        }

        .search-form button {
            padding: 8px 14px;
            cursor: pointer;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ccc;
        }

        th {
            background-color: #f2f2f2;
        }

        td form {
            display: inline;
        }

        td a {
            margin-right: 10px;
        }

        .pagination a {
            padding: 8px 14px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination a:hover {
            background-color: #0056b3;
        }

        .expenses-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .expenses-header h1 {
            margin: 0;
        }

        .export-button {
            display: inline-block;
            padding: 10px 10px;
            background-color: #198754;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            white-space: nowrap;
        }

        .export-button:hover {
            background-color: #146c43;
        }

        @media (max-width: 500px) {
            .expense-header {
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>
<div class="expenses-header">
    <h1>My Expense</h1>
    <a href="/PHP-Learning/expense-tracker/export.php" class="export-button">
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


    <form method="GET" action="" class="search-form">
        <label for="search">Search by title:</label>
       <input type="text" id="search" name="search"
       placeholder="Enter expense title"
       >

       <label for="category">Category:</label>

       <select id="category" name="category">
          <option value="">All Categories</option>
          <option value="food">Food</option>
          <option value="Travel">Travel</option>
</select>

    <label for="from_date">From Date:</label>
    <input type="date" id="from_date" name="from_date">

    <label for="to_date">To Date:</label>
    <input type="date" id="to_date" name="to_date">

    <label for="sort">Sort by:</label>
    <select name="sort" id="sort">
         <option value="date_desc"
            <?= $sort === "date_desc" ? "selected" : "" ?>>
            Newest first
        </option>

        <option value="date_asc"
        <?= $sort === "date_asc" ? "selected" : "" ?>>
        Oldest first
        </option>

        <option value="amount_desc"
        <?= $sort === "amount_desc" ? "selected" : "" ?>>
        Highest amount
        </option>

        <option value="amount_asc"
        <?= $sort === "amount_asc" ? "selected" : "" ?>>
        Lowest amount
        </option>

        <option value="title_asc"
        <?=$sort === "title_asc" ? "selected" : "" ?>>
        Title A-Z
        </option>

        <option value="title_desc"
        <?= $sort === "title_desc" ? "selected" : "" ?>>
        Title Z-A
        </option>
        </select>
       <button type="submit">Search</button>

</form>

    <table border="1">
        <tr>
            <th>Title</th>
            <th>Amount</th>
            <th>Category</th>
            <th>Date</th>
            <th>Action</th>
</tr>

<?php foreach($expenses as $expense): ?>
 <tr>
 <td><?= htmlspecialchars($expense["title"]) ?></td>
 <td><?= htmlspecialchars($expense["amount"]) ?></td>
 <td><?= htmlspecialchars($expense["category"]) ?></td>
 <td><?= htmlspecialchars($expense["expense_date"]) ?></td>
 <td>

 <a href="/PHP-Learning/expense-tracker/edit-expense.php?id=<?= (int) $expense['id'] ?>">
    Edit
</a>
    <form method="POST" action="">
        <input type="hidden" name="expense_id"
         value="<?= (int) $expense["id"] ?>">


         <input type="hidden"
        name="csrf_token"
    value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") ?>"
>
 <button type="submit">Delete</button>

</form>
  </td>

 </tr>

<?php endforeach; ?>
</table>

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
<?php if(isset($_SESSION["success_message"])): ?>
    <p id="success-message" style="color: green;">
        <?= htmlspecialchars($_SESSION["success_message"]) ?>
</p>

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

</body>
</html>
