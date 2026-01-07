<?php

/*
 * This files contains functions that are used in the tests, especially the bootstrap.php file.
 */

/**
 * Returns true if the current test run is a partial run.
 * A partial run is a run that only runs a subset of the tests using the --filter, --testsuite, --group or --exclude-group options.
 */
function isPartialRun(): bool
{
    foreach ($_SERVER['argv'] as $arg) {
        if (str_starts_with((string) $arg, '--filter')) {
            return true;
        }

        if (str_starts_with((string) $arg, '--testsuite')) {
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
