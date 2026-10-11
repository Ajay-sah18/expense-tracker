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

$expenseStatement = $pdo->prepare($sql);
$expenseStatement->execute($params);

$expenses = $expenseStatement->fetchAll(PDO::FETCH_ASSOC);

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
            gap: 10px;
            margin-bottom: 25px;
        }

        .search-form input {
            padding: 8px;
            width: 250px;
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
    </style>
</head>

<body>
    <h1>My Expense</h1>

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

       <button type="submit">Search</button>
       <br><br>
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
<br><br>
 <button type="submit">Delete</button>

</form>
  </td>

 </tr>

<?php endforeach; ?>
</table>

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
