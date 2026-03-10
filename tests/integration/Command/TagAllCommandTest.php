<?php

namespace Wallabag\Tests\Integration\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class TagAllCommandTest extends WallabagKernelTestCase
{
    public function testRunTagAllCommandWithoutUsername()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "username")');

        $application = $this->createApplication();

        $command = $application->find('wallabag:tag:all');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunTagAllCommandWithBadUsername()
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:tag:all');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunTagAllCommand()
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:tag:all');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Tagging entries for user admin...', $tester->getDisplay());
        $this->assertStringContainsString('Done', $tester->getDisplay());
    }
}
