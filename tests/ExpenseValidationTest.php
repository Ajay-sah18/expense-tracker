<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__ . '/../src/ExpenseValidator.php';

class ExpenseValidationTest extends TestCase
{

   public static function expenseAmountProvider(): array
{
    return [
        [250.00, true],
        [100.00, true],
        [50.50, true],
        [0.00, false],
        [-50.00, false],
        ["hello", false],
    ];
}

#[DataProvider('expenseAmountProvider')]
public function testExpenseAmount(
    float|string $amount,
    bool $expected
): void {
    $result = isValidExpenseAmount($amount);

    $this->assertSame($expected, $result);
}

 public function testExpenseTitle() : void
 {
    $this->assertSame(true, isValidExpenseTitle("Lunch"));
    $this->assertSame(true, isValidExpenseTitle("Bus fare"));
    $this->assertSame(false, isValidExpenseTitle(""));
    $this->assertSame(false, isValidExpenseTitle("  "));
    $this->assertSame(true, isValidExpenseTitle("  Food   "));

 }


public function testExpenseCategory(): void
{
    $this->assertSame(true, isValidExpenseCategory("Food"));
    $this->assertSame(true, isValidExpenseCategory("Transport"));
    $this->assertSame(true, isValidExpenseCategory("Pet Care"));
    $this->assertSame(false, isValidExpenseCategory(""));
    $this->assertSame(false, isValidExpenseCategory("   "));
}

public function testExpenseDate(): void
{
    $this->assertSame(true, isValidExpenseDate("2020-10-14"));
    $this->assertSame(true, isValidExpenseDate("2024-02-29"));

    $this->assertSame(false, isValidExpenseDate(""));
    $this->assertSame(false, isValidExpenseDate("2026-02-30"));
    $this->assertSame(false, isValidExpenseDate("14-10-2026"));
    $this->assertSame(false, isValidExpenseDate("hello"));
}
 }