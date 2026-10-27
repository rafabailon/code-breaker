#!/usr/bin/env php

<?php

declare(strict_types=1);

use CodeBreaker\CodeBreaker;
use CodeBreaker\Object\SecretCode;

require __DIR__ . '/../vendor/autoload.php';

$game = new CodeBreaker();
$game->exec(new SecretCode());
