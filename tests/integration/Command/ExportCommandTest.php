<?php

namespace Wallabag\Tests\Integration\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class ExportCommandTest extends WallabagKernelTestCase
{
    protected function tearDown(): void
    {
        @unlink('admin-export.json');
        @unlink('specialexport.json');

        parent::tearDown();
    }

    public function testExportCommandWithoutUsername()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "username")');

        $application = $this->createApplication();

        $command = $application->find('wallabag:export');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testExportCommandWithBadUsername()
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:export');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testExportCommand()
    {
        $application = $this->createApplication();

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
        $application = $this->createApplication();

        $command = $application->find('wallabag:export');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'filepath' => 'specialexport.json',
        ]);

        $this->assertFileExists('specialexport.json');
    }
}
