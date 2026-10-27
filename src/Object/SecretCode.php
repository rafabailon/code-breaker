<?php

declare(strict_types=1);

namespace CodeBreaker\Object;

use InvalidArgumentException;

class SecretCode
{
    public const PLUS = '+';
    public const MINUS = '-';
    public const CHANCES = 10;
    public const SIZE = 4;
    public const MIN_DIGIT = 1;
    public const MAX_DIGIT = 6;

    private array $secretCode;
    private int $currentRound = 1;
    private int $attemptsUsed = 0;
    private bool $victory = false;
    /** @var array<int, array{guess: string, exact: int, misplaced: int}> */
    private array $history = [];

    public function __construct(?array $code = null)
    {
        $this->secretCode = $code ?? $this->generate();

        if (
            count($this->secretCode) !== self::SIZE
            || !array_is_list($this->secretCode)
        ) {
            throw new InvalidArgumentException('A secret code must contain exactly four digits.');
        }

        foreach ($this->secretCode as $digit) {
            if (!is_int($digit) || $digit < self::MIN_DIGIT || $digit > self::MAX_DIGIT) {
                throw new InvalidArgumentException('Secret code digits must be integers from 1 to 6.');
            }
        }
    }

    public function generate(): array
    {
        $code = [];
        for ($i = 0; $i < self::SIZE; $i++) {
            $code[] = random_int(self::MIN_DIGIT, self::MAX_DIGIT);
        }

        return $code;
    }

    public function getCurrentRound(): int
    {
        return $this->currentRound;
    }

    public function getAttemptsUsed(): int
    {
        return $this->attemptsUsed;
    }

    public function getSecretCode(): string
    {
        return implode('', $this->secretCode);
    }

    public function canPlay(): bool
    {
        return $this->currentRound <= self::CHANCES && !$this->victory;
    }

    public function getRemainingAttempts(): int
    {
        if ($this->victory) {
            return 0;
        }

        return max(0, self::CHANCES - $this->attemptsUsed);
    }

    public function getMaxAttempts(): int
    {
        return self::CHANCES;
    }

    public function getCodeLength(): int
    {
        return self::SIZE;
    }

    public function getValidDigits(): string
    {
        return implode('', range(self::MIN_DIGIT, self::MAX_DIGIT));
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function validateNumber(mixed $user): bool
    {
        return is_string($user) && preg_match('/\A[1-6]{4}\z/', $user) === 1;
    }

    public function isVictory(): bool
    {
        return $this->victory;
    }

    public function isGameOver(): bool
    {
        return $this->victory || $this->attemptsUsed >= self::CHANCES;
    }

    public function check(string $userNumber): ?array
    {
        if (!$this->canPlay() || !$this->validateNumber($userNumber)) {
            return null;
        }

        $guess = array_map('intval', str_split($userNumber));
        $secretCounts = array_count_values($this->secretCode);
        $guessCounts = array_count_values($guess);
        $exactMatches = 0;

        foreach ($this->secretCode as $index => $digit) {
            if ($digit === $guess[$index]) {
                $exactMatches++;
            }
        }

        $totalMatches = 0;
        foreach ($secretCounts as $digit => $count) {
            $totalMatches += min($count, $guessCounts[$digit] ?? 0);
        }

        $misplacedMatches = $totalMatches - $exactMatches;
        $this->victory = $exactMatches === self::SIZE;
        $this->attemptsUsed++;
        $this->currentRound++;
        $this->history[] = [
            'guess' => $userNumber,
            'exact' => $exactMatches,
            'misplaced' => $misplacedMatches,
        ];

        return [$exactMatches, $misplacedMatches];
    }

    public function getResultString(string $userNumber): ?string
    {
        $result = $this->check($userNumber);
        if ($result === null) {
            return null;
        }

        return str_repeat(self::PLUS, $result[0]) . str_repeat(self::MINUS, $result[1]);
    }
}
