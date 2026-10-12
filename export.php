<?php

session_start();


if (!isset($_SESSION["user_id"])) {
    header("Location: /PHP-Learning/expense-tracker/auth/login.php");
    exit;
}

require "./config/database.php";

$exportData = $pdo->prepare(
    "SELECT * FROM expenses
    WHERE user_id = :user_id"
);

$exportData->execute([
    ":user_id" => $_SESSION["user_id"]
]);

$expenses = $exportData->fetchAll(PDO::FETCH_ASSOC);

header("Content-Type/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=expenses.csv");

$output = fopen("php://output", "w");

fputcsv($output, [
    "Title",
    "Amount",
    "Category",
    "Expense Date"
]);

foreach ($expenses as $expense) {
    fputcsv($output, [
        $expense["title"],
        $expense["amount"],
        $expense["category"],
        $expense["expense_date"]
    ]);
}

fclose($output);
exit;



