<?php

namespace Tests\Wallabag\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\WallabagTestCase;

class ListUserCommandTest extends WallabagTestCase
{
    public function testRunListUserCommand()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('3/3 user(s) displayed.', $tester->getDisplay());
    }

    public function testRunListUserCommandWithLimit()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([
            '--limit' => 2,
        ]);

        $this->assertStringContainsString('2/3 user(s) displayed.', $tester->getDisplay());
    }

    public function testRunListUserCommandWithSearch()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([
            'search' => 'boss',
        ]);

        $this->assertStringContainsString('1/3 (filtered) user(s) displayed.', $tester->getDisplay());
    }

    public function testRunListUserCommandWithSearchAndLimit()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([
            'search' => 'bo',
            '--limit' => 1,
        ]);

        $this->assertStringContainsString('1/3 (filtered) user(s) displayed.', $tester->getDisplay());
    }
}
