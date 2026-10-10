<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header ("Location: auth/login.php");
    exit;
}

require "config/database.php";

$expenseStatement = $pdo->prepare(
 "SELECT * FROM expenses
    WHERE user_id = :user_id"
);

$expenseStatement->execute([
   ":user_id" => $_SESSION["user_id"]
]);

$expenses = $expenseStatement->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
    <head>
        <title>My Expenses</title>
</head>
<body>
    <h1>My Expense</h1>

    <table border="1">
        <tr>
            <th>Title</th>
            <th>Amount</th>
            <th>Category</th>
            <th>Date</th>
</tr>
<?php foreach($expenses as $expense): ?>
 <tr>
 <td><?= htmlspecialchars($expense["title"]) ?></td>
 <td><?= htmlspecialchars($expense["amount"]) ?></td>
 <td><?= htmlspecialchars($expense["category"]) ?></td>
 <td><?= htmlspecialchars($expense["expense_date"]) ?></td>
 </tr>

<?php endforeach; ?>
</table>
</body>
</html>
