<?php

namespace Tests\Wallabag\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\ImportBundle\Command\ImportCommand;

class ImportCommandTest extends WallabagCoreTestCase
{
    /**
     * @expectedException \Symfony\Component\Console\Exception\RuntimeException
     * @expectedExceptionMessage Not enough arguments
     */
    public function testRunImportCommandWithoutArguments()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ImportCommand());

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\Exception
     * @expectedExceptionMessage not found
     */
    public function testRunImportCommandWithoutFilepath()
    {
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

    /**
     * @expectedException \Doctrine\ORM\NoResultException
     */
    public function testRunImportCommandWithWrongUsername()
    {
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

        $this->assertContains('imported', $tester->getDisplay());
        $this->assertContains('already saved', $tester->getDisplay());
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

        $this->assertContains('imported', $tester->getDisplay());
        $this->assertContains('already saved', $tester->getDisplay());
    }
}
