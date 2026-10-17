<?php

function calculateExpenseShare(float $amount, int $people) : float
{
    if($people <= 0) {
        throw new InvalidArgumentException("Number of people must be greater than zero.");
    }
    return $amount / $people;
}