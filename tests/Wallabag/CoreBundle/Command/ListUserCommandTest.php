<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Command\ListUserCommand;

class ListUserCommandTest extends WallabagCoreTestCase
{
    public function testRunListUserCommand()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ListUserCommand());

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertStringContainsString('3/3 user(s) displayed.', $tester->getDisplay());
    }

    public function testRunListUserCommandWithLimit()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ListUserCommand());

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            '--limit' => 2,
        ]);

        $this->assertStringContainsString('2/3 user(s) displayed.', $tester->getDisplay());
    }

    public function testRunListUserCommandWithSearch()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ListUserCommand());

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'search' => 'boss',
        ]);

        $this->assertStringContainsString('1/3 (filtered) user(s) displayed.', $tester->getDisplay());
    }

    public function testRunListUserCommandWithSearchAndLimit()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ListUserCommand());

        $command = $application->find('wallabag:user:list');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'search' => 'bo',
            '--limit' => 1,
        ]);

        $this->assertStringContainsString('1/3 (filtered) user(s) displayed.', $tester->getDisplay());
    }
}
