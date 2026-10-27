<?php

declare(strict_types=1);

namespace CodeBreaker\Tests;

use CodeBreaker\CodeBreaker;
use CodeBreaker\Object\SecretCode;
use CodeBreaker\View\Console;
use PHPUnit\Framework\TestCase;

final class CodeBreakerTest extends TestCase
{
    public function testInvalidInputDoesNotAdvanceTheRound(): void
    {
        $console = new FakeConsole(['bad', '1234']);
        $game = new SecretCode([1, 2, 3, 4]);

        (new CodeBreaker($console))->exec($game);

        self::assertSame([1, 1], $console->roundsRead);
        self::assertSame(1, $console->invalidInputs);
        self::assertSame([[4, 0]], $console->results);
        self::assertSame(1, $game->getAttemptsUsed());
    }

    public function testConfirmedQuitEndsTheGameEarly(): void
    {
        $console = new FakeConsole(['q'], true);
        $game = new SecretCode([1, 2, 3, 4]);

        (new CodeBreaker($console))->exec($game);

        self::assertTrue($console->endedEarly);
        self::assertSame(0, $game->getAttemptsUsed());
    }

    public function testEndOfInputExitsWithoutThrowing(): void
    {
        $console = new FakeConsole([]);
        $game = new SecretCode([1, 2, 3, 4]);

        (new CodeBreaker($console))->exec($game);

        self::assertTrue($console->endedEarly);
        self::assertSame(0, $game->getAttemptsUsed());
    }
}

final class FakeConsole extends Console
{
    public array $roundsRead = [];
    public array $results = [];
    public int $invalidInputs = 0;
    public bool $endedEarly = false;

    public function __construct(
        private array $inputs,
        private bool $confirmExit = false
    ) {
    }

    public function initGame(): void
    {
    }

    public function read(int $currentRound): ?string
    {
        $this->roundsRead[] = $currentRound;

        return array_shift($this->inputs);
    }

    public function invalidNumber(): void
    {
        $this->invalidInputs++;
    }

    public function numberMatches(int $exactMatches, int $misplacedMatches): void
    {
        $this->results[] = [$exactMatches, $misplacedMatches];
    }

    public function exitGame(): bool
    {
        return $this->confirmExit;
    }

    public function endGame(SecretCode $game, bool $endedEarly = false): void
    {
        $this->endedEarly = $endedEarly;
    }
}
