<?php

namespace Wallabag\CoreBundle\Tests\Command;

use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;
use Wallabag\CoreBundle\Command\InstallCommand;
use Wallabag\CoreBundle\Tests\Mock\InstallCommandMock;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;

class InstallCommandTest extends WallabagCoreTestCase
{
    public static function tearDownAfterClass()
    {
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
        $application->add(new InstallCommandMock());

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

    public function testRunInstallCommandWithReset()
    {
        $this->container = static::$kernel->getContainer();

        $application = new Application(static::$kernel);
        $application->add(new InstallCommandMock());

        $command = $application->find('wallabag:install');

        // We mock the DialogHelper
        $dialog = $this->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $dialog->expects($this->any())
            ->method('ask')
            ->will($this->returnValue('test2'));
        $dialog->expects($this->any())
            ->method('askConfirmation')
            ->will($this->returnValue(true));

        // We override the standard helper with our mock
        $command->getHelperSet()->set($dialog, 'dialog');

        $tester = new CommandTester($command);
        $tester->execute(array(
            'command' => $command->getName(),
            '--reset' => true,
        ));

        $this->assertContains('Step 1 of 4. Checking system requirements.', $tester->getDisplay());
        $this->assertContains('Step 2 of 4. Setting up database.', $tester->getDisplay());
        $this->assertContains('Step 3 of 4. Administration setup.', $tester->getDisplay());
        $this->assertContains('Step 4 of 4. Installing assets.', $tester->getDisplay());

        // we force to reset everything
        $this->assertContains('Droping database, creating database and schema', $tester->getDisplay());
    }

    /**
     * @group command-doctrine
     */
    public function testRunInstallCommandWithDatabaseRemoved()
    {
        $this->container = static::$kernel->getContainer();

        $application = new Application(static::$kernel);
        $application->add(new InstallCommand());
        $application->add(new DropDatabaseDoctrineCommand());

        // drop database first, so the install command won't ask to reset things
        $command = new DropDatabaseDoctrineCommand();
        $command->setApplication($application);
        $command->run(new ArrayInput(array(
            'command' => 'doctrine:database:drop',
            '--force' => true,
        )), new NullOutput());

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

        // the current database doesn't already exist
        $this->assertContains('Creating database and schema, clearing the cache', $tester->getDisplay());
    }

    public function testRunInstallCommandChooseResetSchema()
    {
        $this->container = static::$kernel->getContainer();

        $application = new Application(static::$kernel);
        $application->add(new InstallCommandMock());

        $command = $application->find('wallabag:install');

        // We mock the DialogHelper
        $dialog = $this->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $dialog->expects($this->exactly(3))
            ->method('askConfirmation')
            ->will($this->onConsecutiveCalls(
                false, // don't want to reset the entire database
                true, // do want to reset the schema
                false // don't want to create a new user
            ));

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

        $this->assertContains('Droping schema and creating schema', $tester->getDisplay());
    }

    /**
     * @group command-doctrine
     */
    public function testRunInstallCommandChooseNothing()
    {
        $this->container = static::$kernel->getContainer();

        $application = new Application(static::$kernel);
        $application->add(new InstallCommand());
        $application->add(new DropDatabaseDoctrineCommand());
        $application->add(new CreateDatabaseDoctrineCommand());

        // drop database first, so the install command won't ask to reset things
        $command = new DropDatabaseDoctrineCommand();
        $command->setApplication($application);
        $command->run(new ArrayInput(array(
            'command' => 'doctrine:database:drop',
            '--force' => true,
        )), new NullOutput());

        $this->container->get('doctrine')->getManager()->getConnection()->close();

        $command = new CreateDatabaseDoctrineCommand();
        $command->setApplication($application);
        $command->run(new ArrayInput(array(
            'command' => 'doctrine:database:create',
            '--env' => 'test',
        )), new NullOutput());

        $command = $application->find('wallabag:install');

        // We mock the DialogHelper
        $dialog = $this->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $dialog->expects($this->exactly(2))
            ->method('askConfirmation')
            ->will($this->onConsecutiveCalls(
                false, // don't want to reset the entire database
                false // don't want to create a new user
            ));

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

        $this->assertContains('Creating schema', $tester->getDisplay());
    }

    public function testRunInstallCommandNoInteraction()
    {
        $this->container = static::$kernel->getContainer();

        $application = new Application(static::$kernel);
        $application->add(new InstallCommandMock());

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
            '--no-interaction' => true,
        ));

        $this->assertContains('Step 1 of 4. Checking system requirements.', $tester->getDisplay());
        $this->assertContains('Step 2 of 4. Setting up database.', $tester->getDisplay());
        $this->assertContains('Step 3 of 4. Administration setup.', $tester->getDisplay());
        $this->assertContains('Step 4 of 4. Installing assets.', $tester->getDisplay());
    }
}
