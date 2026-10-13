
<?php

require __DIR__ . "/config/auth.php";
require __DIR__ . "/config/database.php";

// Fetch only the logged-in user's expenses.
$exportData = $pdo->prepare(
    "SELECT title, amount, category, expense_date
     FROM expenses
     WHERE user_id = :user_id"
);

$exportData->execute([
    ":user_id" => $_SESSION["user_id"]
]);

$expenses = $exportData->fetchAll(PDO::FETCH_ASSOC);

// Protect text cells against spreadsheet formula injection.
function safeCsvText($value)
{
    $value = (string) $value;

    if (preg_match('/^[\t\r\n ]*[=+\-@]/', $value)) {
        return "'" . $value;
    }

    return $value;
}

// Send CSV download headers.
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=expenses.csv");
header("X-Content-Type-Options: nosniff");

$output = fopen("php://output", "w");

// Add a UTF-8 BOM for better compatibility with Excel.
fwrite($output, "\xEF\xBB\xBF");

// Write the column headings.
fputcsv($output, [
    "Title",
    "Amount",
    "Category",
    "Expense Date"
]);

// Write the expense records.
foreach ($expenses as $expense) {
    fputcsv($output, [
        safeCsvText($expense["title"]),
        $expense["amount"],
        safeCsvText($expense["category"]),
        $expense["expense_date"]
    ]);
}

fclose($output);
exit;
