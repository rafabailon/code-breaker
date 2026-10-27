<?php

declare(strict_types=1);

namespace CodeBreaker;

use CodeBreaker\Object\SecretCode;
use CodeBreaker\View\Console;

class CodeBreaker
{
    private Console $view;

    public function __construct(?Console $view = null)
    {
        $this->view = $view ?? new Console();
    }

    public function exec(SecretCode $game): void
    {
        $this->view->initGame();
        $endedEarly = false;

        while ($game->canPlay()) {
            $userNumber = $this->view->read($game->getCurrentRound());
            if ($userNumber === null) {
                $endedEarly = true;
                break;
            }

            if (strcasecmp($userNumber, 'q') === 0) {
                if ($this->view->exitGame()) {
                    $endedEarly = true;
                    break;
                }

                continue;
            }

            if (!$game->validateNumber($userNumber)) {
                $this->view->invalidNumber();
                continue;
            }

            $result = $game->check($userNumber);
            if ($result !== null) {
                $this->view->numberMatches($result[0], $result[1]);
            }
        }

        $this->view->endGame($game, $endedEarly);
    }
}
