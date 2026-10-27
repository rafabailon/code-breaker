<?php

declare(strict_types=1);

namespace CodeBreaker\View;

use CodeBreaker\Object\SecretCode;

class Console
{
    public function initGame(): void
    {
        fwrite(STDOUT, "Code Breaker started! Let's play.\n");
        fwrite(STDOUT, "Enter exactly " . SecretCode::SIZE . " digits from "
            . SecretCode::MIN_DIGIT . " to " . SecretCode::MAX_DIGIT . ".\n");
        fwrite(STDOUT, "You have " . SecretCode::CHANCES . " attempts. Enter 'q' to exit.\n\n");
    }

    public function read(int $currentRound): ?string
    {
        fwrite(STDOUT, 'Enter a ' . SecretCode::SIZE . '-digit code (Round ' . $currentRound . '): ');
        $input = fgets(STDIN);

        return $input === false ? null : trim($input);
    }

    public function invalidNumber(): void
    {
        fwrite(
            STDOUT,
            'Invalid code. Enter exactly ' . SecretCode::SIZE
            . ' digits, each from ' . SecretCode::MIN_DIGIT . ' to ' . SecretCode::MAX_DIGIT . ".\n"
        );
    }

    public function numberMatches(int $exactMatches, int $misplacedMatches): void
    {
        $result = str_repeat(SecretCode::PLUS, $exactMatches)
            . str_repeat(SecretCode::MINUS, $misplacedMatches);
        fwrite(STDOUT, "Result: $result\n");
    }

    public function exitGame(): bool
    {
        fwrite(STDOUT, "\nAre you sure you want to exit the game? (y/n): ");
        $input = fgets(STDIN);

        return $input !== false && strtolower(trim($input)) === 'y';
    }

    public function endGame(SecretCode $game, bool $endedEarly = false): void
    {
        if ($game->isVictory()) {
            fwrite(STDOUT, "\nCongratulations, you have won!\n");
            fwrite(
                STDOUT,
                'You guessed the secret code ' . $game->getSecretCode()
                . ' in ' . $game->getAttemptsUsed() . " attempts.\n"
            );

            return;
        }

        if ($endedEarly) {
            fwrite(STDOUT, "\nGame ended. The secret code was " . $game->getSecretCode() . ".\n");

            return;
        }

        fwrite(STDOUT, "\nSorry, you have lost!\n");
        fwrite(STDOUT, 'The secret code is ' . $game->getSecretCode() . ".\n");
    }
}
