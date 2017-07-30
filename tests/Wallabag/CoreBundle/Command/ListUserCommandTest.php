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

        $this->assertContains('3 user(s) displayed.', $tester->getDisplay());
    }
}
