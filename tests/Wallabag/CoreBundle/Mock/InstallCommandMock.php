<?php

namespace Tests\Wallabag\CoreBundle\Mock;

use Wallabag\CoreBundle\Command\InstallCommand;

/**
 * This mock aims to speed the test of InstallCommand by avoid calling external command
 * like all doctrine commands.
 *
 * This speed the test but as a downside, it doesn't allow to fully test the InstallCommand
 *
 * Launching tests to avoid doctrine command:
 *     phpunit --exclude-group command-doctrine
 */
class InstallCommandMock extends InstallCommand
{
    protected function runCommand($command, $parameters = [])
    {
        return $this;
    }
}
