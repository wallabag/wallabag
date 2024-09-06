<?php

namespace Tests\Wallabag\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\WallabagTestCase;

class ExportCommandTest extends WallabagTestCase
{
    public function testExportCommandWithoutUsername()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "username")');

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:export');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testExportCommandWithBadUsername()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:export');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testExportCommand()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:export');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Exporting 6 entrie(s) for user admin...', $tester->getDisplay());
        $this->assertStringContainsString('Done', $tester->getDisplay());
        $this->assertFileExists('admin-export.json');
    }

    public function testExportCommandWithSpecialPath()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:export');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'filepath' => 'specialexport.json',
        ]);

        $this->assertFileExists('specialexport.json');
    }
}
