<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\CoreBundle\Command\CleanDuplicatesCommand;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class CleanDuplicatesCommandTest extends WallabagCoreTestCase
{
    public function testRunTagAllCommandForAll()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new CleanDuplicatesCommand());

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertContains('Cleaning through 3 user accounts', $tester->getDisplay());
        $this->assertContains('Finished cleaning. 0 duplicates found in total', $tester->getDisplay());
    }

    public function testRunTagAllCommandWithBadUsername()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new CleanDuplicatesCommand());

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'unknown',
        ]);

        $this->assertContains('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunTagAllCommandForUser()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new CleanDuplicatesCommand());

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
        ]);

        $this->assertContains('Cleaned 0 duplicates for user admin', $tester->getDisplay());
    }
}
