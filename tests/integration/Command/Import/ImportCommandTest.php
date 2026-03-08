<?php

namespace Wallabag\Tests\Integration\Command\Import;

use Doctrine\ORM\NoResultException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class ImportCommandTest extends WallabagKernelTestCase
{
    public function testRunImportCommandWithoutArguments()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $application = $this->createApplication();

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunImportCommandWithoutFilepath()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('not found');

        $application = $this->createApplication();

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'filepath' => 1,
        ]);
    }

    public function testRunImportCommandWithWrongUsername()
    {
        $this->expectException(NoResultException::class);

        $application = $this->createApplication();

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'random',
            'filepath' => './',
        ]);
    }

    public function testRunImportCommand()
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'filepath' => static::getContainer()->getParameter('kernel.project_dir') . '/tests/fixtures/Import/wallabag-v2-read.json',
            '--importer' => 'v2',
        ]);

        $this->assertStringContainsString('imported', $tester->getDisplay());
        $this->assertStringContainsString('already saved', $tester->getDisplay());
    }

    public function testRunImportCommandWithUserId()
    {
        $application = $this->createApplication();
        $userId = $this->getUser('admin')->getId();

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => $userId,
            'filepath' => static::getContainer()->getParameter('kernel.project_dir') . '/tests/fixtures/Import/wallabag-v2-read.json',
            '--useUserId' => true,
            '--importer' => 'v2',
        ]);

        $this->assertStringContainsString('imported', $tester->getDisplay());
        $this->assertStringContainsString('already saved', $tester->getDisplay());
    }
}
