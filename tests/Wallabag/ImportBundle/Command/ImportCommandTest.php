<?php

namespace Tests\Wallabag\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\ImportBundle\Command\ImportCommand;

class ImportCommandTest extends WallabagCoreTestCase
{
    public function testRunImportCommandWithoutArguments()
    {
        $this->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $application = new Application($this->getClient()->getKernel());
        $application->add(new ImportCommand());

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);
    }

    public function testRunImportCommandWithoutFilepath()
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\Exception::class);
        $this->expectExceptionMessage('not found');

        $application = new Application($this->getClient()->getKernel());
        $application->add(new ImportCommand());

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
            'filepath' => 1,
        ]);
    }

    public function testRunImportCommandWithWrongUsername()
    {
        $this->expectException(\Doctrine\ORM\NoResultException::class);

        $application = new Application($this->getClient()->getKernel());
        $application->add(new ImportCommand());

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'random',
            'filepath' => './',
        ]);
    }

    public function testRunImportCommand()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ImportCommand());

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
            'filepath' => $application->getKernel()->getContainer()->getParameter('kernel.project_dir') . '/tests/Wallabag/ImportBundle/fixtures/wallabag-v2-read.json',
            '--importer' => 'v2',
        ]);

        $this->assertStringContainsString('imported', $tester->getDisplay());
        $this->assertStringContainsString('already saved', $tester->getDisplay());
    }

    public function testRunImportCommandWithUserId()
    {
        $this->logInAs('admin');

        $application = new Application($this->getClient()->getKernel());
        $application->add(new ImportCommand());

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => $this->getLoggedInUserId(),
            'filepath' => $application->getKernel()->getContainer()->getParameter('kernel.project_dir') . '/tests/Wallabag/ImportBundle/fixtures/wallabag-v2-read.json',
            '--useUserId' => true,
            '--importer' => 'v2',
        ]);

        $this->assertStringContainsString('imported', $tester->getDisplay());
        $this->assertStringContainsString('already saved', $tester->getDisplay());
    }
}
