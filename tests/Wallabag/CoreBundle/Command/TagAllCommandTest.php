<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Command\TagAllCommand;

class TagAllCommandTest extends WallabagCoreTestCase
{
    public function testRunTagAllCommandWithoutUsername()
    {
        $this->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "username")');

        $application = new Application($this->getClient()->getKernel());
        $application->add(new TagAllCommand());

        $command = $application->find('wallabag:tag:all');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);
    }

    public function testRunTagAllCommandWithBadUsername()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new TagAllCommand());

        $command = $application->find('wallabag:tag:all');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunTagAllCommand()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new TagAllCommand());

        $command = $application->find('wallabag:tag:all');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Tagging entries for user admin...', $tester->getDisplay());
        $this->assertStringContainsString('Done', $tester->getDisplay());
    }
}
