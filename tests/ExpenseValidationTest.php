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

//using TDD(Test-Driven Development) test

public function testValidationTest() : void
{
    $this->assertSame(true, isValidExpenseAmount(100));
}

public function testZeroAmountIsInvalid(): void
{
    $this->assertSame(false, isValidExpenseAmount(0));
}

public function testInvalidCalendarDateIsRejected() : void
{
    $this->assertSame(false, isValidExpenseDate("2026-02-30"));

}

public function testDateWithWhitespaceIsRejected() : void
{
 $this->assertSame(false, isValidExpenseDate("  2026-10-15  "));
}

public function testDateWithOverflowIsRejected() : void
{
    $this->assertSame(false, isValidExpenseDate("2026-13-01"));
}

 public function testWhitespaceCharactersOnlyTitleIsRejected() : void
 {
    $this->assertSame(false, isValidExpenseTitle("\t\n"));
 }

 public function testInvalidExpenseCategoryIsRejected(): void
 {
    $this->assertSame(false, isAllowedExpenseCategory("InvalidCategory"));
 }

  public function testValidExpenseCategoryIsAccepted(): void
  {
 $this->assertSame(true, isAllowedExpenseCategory("Food"));
  }

public function testCategoryWithSurroundingWhitespaceIsRejected(): void
{
    $this->assertSame(false, isAllowedExpenseCategory(" Food "));
}


public function testEmptyCategoryIsRejected(): void
{
    $this->assertSame(false, isAllowedExpenseCategory(""));
}


public function testCategoryWithDifferentCaseIsRejected(): void
{
    $this->assertSame(false, isAllowedExpenseCategory("food"));
}


public function testWhitespaceOnlyCategoryIsRejected(): void
{
    $this->assertSame(false, isAllowedExpenseCategory("   "));
}

public function testExpenseCategoryIsValidForExpense(): void
{
    $this->assertSame(true, isValidExpenseCategoryForExpense("Food"));
}

public function testInvalidCategoryIsInvalidForExpense(): void
{
    $this->assertSame(
        false,
        isValidExpenseCategoryForExpense("InvalidCategory")
    );
}

public function testCategoryWithWhitespaceIsInvalidForExpense(): void
{
    $this->assertSame(
        false,
        isValidExpenseCategoryForExpense(" Food ")
    );
}

public function testCategoryWithDifferentCaseIsInvalidForExpense(): void
{
    $this->assertSame(
        false,
        isValidExpenseCategoryForExpense("food")
    );
}




#[DataProvider('allowedCategoriesProvider')]
public function testAllowedExpenseCategoriesAreAccepted(string $category): void
{
    $this->assertSame(true, isAllowedExpenseCategory($category));
}

public static function allowedCategoriesProvider(): array
{
    return [
        ['Food'],
        ['Transport'],
        ['Shopping'],
        ['Bills'],
        ['Education'],
        ['Health'],
        ['Entertainment'],
        ['Other'],
    ];
}


}