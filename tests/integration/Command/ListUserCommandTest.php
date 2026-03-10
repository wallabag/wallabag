<?php

namespace Wallabag\Tests\Integration\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class ListUserCommandTest extends WallabagKernelTestCase
{
    public function testRunListUserCommand()
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('3/3 user(s) displayed.', $tester->getDisplay());
    }

    public function testRunListUserCommandWithLimit()
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([
            '--limit' => 2,
        ]);

        $this->assertStringContainsString('2/3 user(s) displayed.', $tester->getDisplay());
    }

    public function testRunListUserCommandWithSearch()
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([
            'search' => 'boss',
        ]);

        $this->assertStringContainsString('1/3 (filtered) user(s) displayed.', $tester->getDisplay());
    }

    public function testRunListUserCommandWithSearchAndLimit()
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([
            'search' => 'bo',
            '--limit' => 1,
        ]);

        $this->assertStringContainsString('1/3 (filtered) user(s) displayed.', $tester->getDisplay());
    }
}
