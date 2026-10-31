<?php

function calculateExpenseShare(float $amount, int $people) : float
{
    if($people <= 0) {
        throw new InvalidArgumentException("Number of people must be greater than zero.");
    }
    return $amount / $people;
}

class ExpenseCalculator
{
    public function calculateTotal(array $amounts): float
{
    foreach ($amounts as $amount) {
       if (!is_int($amount) && !is_float($amount)) {
    throw new InvalidArgumentException(
        "Expense amounts must be integers or floats."
    );
}

        if ($amount <= 0) {
            throw new InvalidArgumentException(
                "Expense amounts must be greater than zero."
            );
        }
    }
    return array_sum($amounts);
}
public static function calculateMaximumExpense(array $amounts): int|float
{
    if(empty($amounts)) {
        throw new InvalidArgumentException(
            "Expense amounts cannot be empty."
        );
    }
    return max($amounts);
}

}