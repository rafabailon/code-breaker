<?php

declare(strict_types=1);

namespace CodeBreaker\Tests;

use CodeBreaker\Object\SecretCode;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SecretCodeTest extends TestCase
{
    #[DataProvider('matchingExamples')]
    public function testScoresDocumentedExamples(string $secret, string $guess, array $expected): void
    {
        $game = new SecretCode(array_map('intval', str_split($secret)));

        self::assertSame($expected, $game->check($guess));
    }

    public static function matchingExamples(): iterable
    {
        yield 'one exact and one misplaced' => ['1234', '4256', [1, 1]];
        yield 'three exact' => ['3435', '3445', [3, 0]];
        yield 'one exact duplicate guess' => ['4342', '1122', [1, 0]];
        yield 'one exact and one misplaced with duplicates' => ['2314', '3434', [1, 1]];
    }

    public function testCountsRepeatedDigitsWithoutOvermatching(): void
    {
        $game = new SecretCode([1, 1, 2, 2]);

        self::assertSame([1, 2], $game->check('1213'));
    }

    public function testRejectsMalformedGuessesWithoutUsingAnAttempt(): void
    {
        $game = new SecretCode([1, 2, 3, 4]);

        self::assertFalse($game->validateNumber('123'));
        self::assertFalse($game->validateNumber('1237'));
        self::assertFalse($game->validateNumber('12a4'));
        self::assertFalse($game->validateNumber(1234));
        self::assertNull($game->check('0000'));
        self::assertSame(0, $game->getAttemptsUsed());
        self::assertSame(1, $game->getCurrentRound());
    }

    public function testValidatesSecretCodeShapeAndDigits(): void
    {
        self::expectException(InvalidArgumentException::class);
        new SecretCode([1, 2, 3]);
    }

    public function testRejectsOutOfRangeSecretDigits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SecretCode([1, 2, 3, 7]);
    }

    public function testRejectsNonIntegerSecretDigits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SecretCode([1, 2, 3, '4']);
    }

    public function testWinStopsTheGameAndReportsTheCorrectAttemptCount(): void
    {
        $game = new SecretCode([1, 2, 3, 4]);

        self::assertSame([4, 0], $game->check('1234'));
        self::assertTrue($game->isVictory());
        self::assertFalse($game->canPlay());
        self::assertSame(1, $game->getAttemptsUsed());
        self::assertNull($game->check('1234'));
    }

    public function testGameEndsAfterTenValidGuesses(): void
    {
        $game = new SecretCode([1, 2, 3, 4]);

        for ($attempt = 0; $attempt < SecretCode::CHANCES; $attempt++) {
            self::assertNotNull($game->check('6666'));
        }

        self::assertFalse($game->canPlay());
        self::assertSame(SecretCode::CHANCES, $game->getAttemptsUsed());
        self::assertNull($game->check('6666'));
    }

    public function testGeneratedCodeHasFourDigitsInTheAllowedRange(): void
    {
        $game = new SecretCode();
        $code = $game->getSecretCode();

        self::assertMatchesRegularExpression('/\A[1-6]{4}\z/', $code);
    }

    public function testProvidesGameStatisticsAndHistory(): void
    {
        $game = new SecretCode([1, 2, 3, 4]);

        self::assertSame(10, $game->getMaxAttempts());
        self::assertSame(4, $game->getCodeLength());
        self::assertSame('123456', $game->getValidDigits());
        self::assertSame(10, $game->getRemainingAttempts());
        self::assertFalse($game->isGameOver());

        self::assertSame([1, 1], $game->check('4256'));
        self::assertSame(9, $game->getRemainingAttempts());
        self::assertCount(1, $game->getHistory());
        self::assertSame('4256', $game->getHistory()[0]['guess']);
    }

    public function testGetResultString(): void
    {
        $game = new SecretCode([1, 2, 3, 4]);
        self::assertSame('+-', $game->getResultString('4256'));
        self::assertNull($game->getResultString('invalid'));
    }

    public function testGameOverState(): void
    {
        $game = new SecretCode([1, 2, 3, 4]);
        $game->check('1234');
        self::assertTrue($game->isVictory());
        self::assertTrue($game->isGameOver());
        self::assertSame(0, $game->getRemainingAttempts());
    }
}
