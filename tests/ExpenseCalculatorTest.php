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

    public static function invalidPeopleProvider(): array
    {
        return [
            [0],
            [-1],
            [-5]
        ];
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
}