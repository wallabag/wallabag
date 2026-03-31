<?php

use Symfony\Component\Process\Process;

/*
 * This files contains functions that are used in the tests, especially the bootstrap.php file.
 */

function getCurrentTestSuite(): ?string
{
    foreach ($_SERVER['argv'] ?? [] as $index => $arg) {
        if ('--testsuite' === $arg) {
            $suite = $_SERVER['argv'][$index + 1] ?? null;

            return null === $suite ? null : (string) $suite;
        }

        if (str_starts_with((string) $arg, '--testsuite=')) {
            return substr((string) $arg, strlen('--testsuite='));
        }
    }

    return null;
}

/**
 * Returns true if the current test run is a partial run.
 * A partial run is a run that only runs a subset of the tests using the --filter, --group or --exclude-group options.
 */
function isPartialRun(): bool
{
    foreach ($_SERVER['argv'] as $arg) {
        if (str_starts_with((string) $arg, '--filter')) {
            return true;
        }

        if (str_starts_with((string) $arg, '--group')) {
            return true;
        }

        if (str_starts_with((string) $arg, '--exclude-group')) {
            return true;
        }
    }

    return false;
}

function runBootstrapCommand(array $command, bool $mustSucceed = true): void
{
    $process = new Process($command);

    if ($mustSucceed) {
        $process->mustRun(static function ($type, $buffer): void {
            echo $buffer;
        });

        return;
    }

    $process->run(static function ($type, $buffer): void {
        echo $buffer;
    });
}
