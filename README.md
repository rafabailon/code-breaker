# Code Breaker

Code Breaker is a command-line implementation of the classic code-breaking game (also known as Mastermind). The application generates a secret four-digit code and gives the player up to ten valid guesses to discover it.

## Requirements

- PHP 8.2 or newer
- Composer 2
- Docker Compose (optional)

The game has no database, web server, or runtime package dependencies. PHPUnit is installed as a development dependency for the automated test suite.

## Install and play

```sh
composer install
php bin/play_game.php
```

Enter four digits from `1` through `6` for each guess. Enter `q` to request exit; the game asks for confirmation. Invalid guesses do not use an attempt. End-of-file (for example, pressing Ctrl+D) exits cleanly.

## Rules and scoring

- The secret code and each guess contain exactly four digits from `1` to `6`.
- Repeated digits are allowed.
- A `+` is one correct digit in the correct position.
- A `-` is one correct digit in a different position.
- Results always show all `+` signs before all `-` signs; they do not identify positions.
- A guess wins only when it has four exact matches.
- The player has ten valid guesses. Invalid input does not consume a guess.

Examples:

| Secret | Guess | Result |
| --- | --- | --- |
| `1234` | `4256` | `+-` |
| `3435` | `3445` | `+++` |
| `4342` | `1122` | `+` |
| `2314` | `3434` | `+-` |

The third example is intentionally corrected from the original project documentation: only one `2` occurs in both the secret and guess, and it is in the same position, so the correct result is `+`, not `+-`.

## Automated tests

```sh
composer test
```

The PHPUnit suite covers the documented examples, duplicate-digit scoring, input and secret-code validation, attempt limits, win state, and the command-line game loop.

## Docker

Docker is an optional PHP CLI environment. It does not start a web server or database:

```sh
docker compose run --rm app composer install
docker compose run --rm app composer test
docker compose run --rm app php bin/play_game.php
```

Each command removes its one-off container on exit. To stop and remove any Compose-managed containers, run:

```sh
docker compose down --remove-orphans
```

## Project structure

- `src/CodeBreaker.php` coordinates the game loop and console.
- `src/Object/SecretCode.php` generates and validates codes, tracks game state, scores guesses, and provides game history/stats.
- `src/View/Console.php` handles terminal input and output.
- `bin/play_game.php` is the executable entry point.
- `tests/` contains the automated PHPUnit tests.
- `docker/php-cli/` defines the optional containerized CLI runtime.

## Development commands

The `Makefile` provides shortcuts:

```sh
make install
make test
make play
make docker-test
make clean
make docker-clean
```

`make clean` removes PHPUnit's generated cache without removing installed Composer dependencies. `make docker-clean` stops and removes Compose-managed containers and networks.
