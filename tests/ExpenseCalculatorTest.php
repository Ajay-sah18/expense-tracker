<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__ . '/../src/ExpenseCalculator.php';

class ExpenseCalculatorTest extends TestCase
{
    public function testExpenseShareIsCalculatedCorrectly(): void
    {
        $result = calculateExpenseShare(100,4);

        $this->assertSame(25.0, $result);
    }

    public function testZeroPeopleThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(
            "Number of people must be greater than zero."
        );
        calculateExpenseShare(100, 0);
    }

    public function testCalculateTotalReturnsCorrectTotal(): void
    {
       $calculator = new ExpenseCalculator();
       $amounts = [12.50, 7.50, 20.00];

       $total = $calculator->calculateTotal($amounts);

       $this->assertSame(40.00, $total);
    }

     public function testCalculateTotalReturnsZeroForEmptyArray(): void
     {
        $calculator = new ExpenseCalculator();
        $amounts = [];

        $total = $calculator->calculateTotal($amounts);

        $this->assertSame(0.0, $total);
     }

     public function testCalculateTotalThrowsExceptionForNegativeAmount(): void
     {
        $calculator = new ExpenseCalculator();

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculateTotal([-10.0, 20.0]);

     }

     public function testCalculateTotalThrowsExceptionForZeroAmount(): void
     {
        $calculator = new ExpenseCalculator();

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculateTotal([0.0, 20.0]);
     }

     public function testCalculateTotalThrowsExceptionForNonNumericAmount(): void
     {
       $calculator = new ExpenseCalculator();

       $this->expectException(InvalidArgumentException::class);

       $calculator->calculateTotal([10.00, "Hello", 20.0]);
     }

     public function testCalculateTotalThrowsExceptionForNumericString(): void
     {
        $calculator = new ExpenseCalculator();

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculateTotal(["12.50", 20.0]);
     }



public function testCalculateExpenseShareRejectsNegativePeople(): void
{
    $this->expectException(InvalidArgumentException::class);

    calculateExpenseShare(100, -1);
}

public function testCalculateExpenseShareRejectsZeroPeople(): void
{
    $this->expectException(InvalidArgumentException::class);

    calculateExpenseShare(100, 0);
}

public function testCalculateExpenseShareWithOnePerson(): void
{
    $result = calculateExpenseShare(100, 1);

    $this->assertSame(100.0, $result);
}

public static function expenseShareProvider(): array
{
    return [
        [100, 1, 100.0],
        [100, 2, 50.0],
        [100, 4, 25.0],
        [200, 4, 50.0],
    ];
}

public function testCalculateMaximumExpense(): void
{
    $amounts = [100, 250, 50, 400];

    $result = ExpenseCalculator::calculateMaximumExpense($amounts);

    $this->assertSame(400, $result);
}

public function testCalculateMaximumExpenseThrowsExceptionForEmptyArray(): void
{
    $amounts = [];

 $this->expectException(InvalidArgumentException::class);

     ExpenseCalculator::calculateMaximumExpense($amounts);
}

    #[DataProvider('invalidPeopleProvider')]
    public function testInvalidPeopleCountThrowsException(int $people): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Number of people must be greater than zero."
        );

        calculateExpenseShare(100, $people);
    }

   public static function invalidPeopleProvider(): array
{
    return [
        [0],
        [-1],
        [-5]
    ];
}


#[DataProvider('expenseShareProvider')]
public function testCalculateExpenseShareWithDataProvider(
    float $amount,
    int $people,
    float $expected
): void {
    $result = calculateExpenseShare($amount, $people);

    $this->assertSame($expected, $result);
}

}