<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Command\ExportCommand;

class ExportCommandTest extends WallabagCoreTestCase
{
    /**
     * @expectedException \Symfony\Component\Console\Exception\RuntimeException
     * @expectedExceptionMessage Not enough arguments (missing: "username")
     */
    public function testExportCommandWithoutUsername()
    {
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

        $this->assertContains('User "unknown" not found', $tester->getDisplay());
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

        $this->assertContains('Exporting 5 entrie(s) for user admin...', $tester->getDisplay());
        $this->assertContains('Done', $tester->getDisplay());
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
