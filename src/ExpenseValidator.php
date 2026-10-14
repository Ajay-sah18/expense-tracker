<?php

function isValidExpenseAmount(mixed $amount): bool
{
    if (!is_numeric($amount)) {
        return false;
    }

    $amount = (float) $amount;

    if (!is_finite($amount)) {
        return false;
    }

    return $amount > 0;

}

function isValidExpenseTitle(string $title): bool
{
 $title = trim($title);

 if ($title === "") {
    return false;
 }
 return true;
}

function isValidExpenseCategory(string $category): bool
{
    $category = trim($category);

    if ($category === "") {
        return false;
    }

    return true;
}

function isValidExpenseDate(string $date): bool
{
    $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

    if ($dateObject === false){
        return false;
    }
    return $dateObject->format('Y-m-d') === $date;
}