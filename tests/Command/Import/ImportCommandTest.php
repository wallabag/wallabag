<?php

namespace Tests\Wallabag\Command\Import;

use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\WallabagTestCase;

class ImportCommandTest extends WallabagTestCase
{
    public function testRunImportCommandWithoutArguments()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunImportCommandWithoutFilepath()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('not found');

        $application = new Application($this->getTestClient()->getKernel());

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

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'random',
            'filepath' => './',
        ]);
    }

    public function testRunImportCommand()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'filepath' => $application->getKernel()->getContainer()->getParameter('kernel.project_dir') . '/tests/fixtures/Import/wallabag-v2-read.json',
            '--importer' => 'v2',
        ]);

        $this->assertStringContainsString('imported', $tester->getDisplay());
        $this->assertStringContainsString('already saved', $tester->getDisplay());
    }

    public function testRunImportCommandWithUserId()
    {
        $this->logInAs('admin');

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => $this->getLoggedInUserId(),
            'filepath' => $application->getKernel()->getContainer()->getParameter('kernel.project_dir') . '/tests/fixtures/Import/wallabag-v2-read.json',
            '--useUserId' => true,
            '--importer' => 'v2',
        ]);

        $this->assertStringContainsString('imported', $tester->getDisplay());
        $this->assertStringContainsString('already saved', $tester->getDisplay());
    }
}
