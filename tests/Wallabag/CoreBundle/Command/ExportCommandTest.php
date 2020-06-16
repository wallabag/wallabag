<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Command\ExportCommand;

class ExportCommandTest extends WallabagCoreTestCase
{
    public function testExportCommandWithoutUsername()
    {
        $this->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "username")');

        $application = new Application($this->getClient()->getKernel());
        $application->add(new ExportCommand());

        $command = $application->find('wallabag:export');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);
    }

    public function testExportCommandWithBadUsername()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ExportCommand());

        $command = $application->find('wallabag:export');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testExportCommand()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ExportCommand());

        $command = $application->find('wallabag:export');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Exporting 5 entrie(s) for user admin...', $tester->getDisplay());
        $this->assertStringContainsString('Done', $tester->getDisplay());
        $this->assertFileExists('admin-export.json');
    }

    public function testExportCommandWithSpecialPath()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ExportCommand());

        $command = $application->find('wallabag:export');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
            'filepath' => 'specialexport.json',
        ]);

        $this->assertFileExists('specialexport.json');
    }
}
