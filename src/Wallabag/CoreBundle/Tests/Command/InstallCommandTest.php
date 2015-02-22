<?php

namespace Wallabag\CoreBundle\Tests\Command;

use Wallabag\CoreBundle\Tests\WallabagTestCase;
use Wallabag\CoreBundle\Command\InstallCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class InstallCommandTest extends WallabagTestCase
{
    public function tearDown()
    {
        parent::tearDown();

        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $code = $application->run(new ArrayInput(array(
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true,
            '--env' => 'test',
        )), new NullOutput());
    }

    public function testRunInstallCommand()
    {
        $this->container = static::$kernel->getContainer();

        $application = new Application(static::$kernel);
        $application->add(new InstallCommand());

        $command = $application->find('wallabag:install');

        // We mock the DialogHelper
        $dialog = $this->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $dialog->expects($this->any())
            ->method('ask')
            ->will($this->returnValue('test'));
        $dialog->expects($this->any())
            ->method('askConfirmation')
            ->will($this->returnValue(true));

        // We override the standard helper with our mock
        $command->getHelperSet()->set($dialog, 'dialog');

        $tester = new CommandTester($command);
        $tester->execute(array(
            'command' => $command->getName(),
        ));

        $this->assertContains('Step 1 of 4. Checking system requirements.', $tester->getDisplay());
        $this->assertContains('Step 2 of 4. Setting up database.', $tester->getDisplay());
        $this->assertContains('Step 3 of 4. Administration setup.', $tester->getDisplay());
        $this->assertContains('Step 4 of 4. Installing assets.', $tester->getDisplay());
    }
}
