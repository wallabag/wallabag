<?php

namespace Tests\Wallabag\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\ImportBundle\Command\ImportCommand;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class ImportCommandTest extends WallabagCoreTestCase
{
    /**
     * @expectedException Symfony\Component\Console\Exception\RuntimeException
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
     * @expectedException Symfony\Component\Config\Definition\Exception\Exception
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
            'userId' => 1,
            'filepath' => 1,
        ]);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\Exception
     * @expectedExceptionMessage User with id
     */
    public function testRunImportCommandWithoutUserId()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ImportCommand());

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'userId' => 0,
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
            'userId' => 1,
            'filepath' => $application->getKernel()->getContainer()->getParameter('kernel.root_dir').'/../tests/Wallabag/ImportBundle/fixtures/wallabag-v2-read.json',
            '--importer' => 'v2',
        ]);

        $this->assertContains('imported', $tester->getDisplay());
        $this->assertContains('already saved', $tester->getDisplay());
    }
}
