<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\CoreBundle\Command\InstallCommand;
use Tests\Wallabag\CoreBundle\Mock\InstallCommandMock;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class InstallCommandTest extends WallabagCoreTestCase
{
    public function setUp()
    {
        parent::setUp();

        if ($this->getClient()->getContainer()->get('doctrine')->getConnection()->getDriver() instanceof \Doctrine\DBAL\Driver\PDOPgSql\Driver) {
            /*
             * LOG:  statement: CREATE DATABASE "wallabag"
             * ERROR:  source database "template1" is being accessed by other users
             * DETAIL:  There is 1 other session using the database.
             * STATEMENT:  CREATE DATABASE "wallabag"
             * FATAL:  database "wallabag" does not exist
             *
             * http://stackoverflow.com/a/14374832/569101
             */
            $this->markTestSkipped('PostgreSQL spotted: can find a good way to drop current database, skipping.');
        }
    }

    public static function tearDownAfterClass()
    {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $code = $application->run(new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true,
            '--env' => 'test',
        ]), new NullOutput());
    }

    public function testRunInstallCommand()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new InstallCommandMock());

        $command = $application->find('wallabag:install');

        // We mock the QuestionHelper
        $question = $this->getMockBuilder('Symfony\Component\Console\Helper\QuestionHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $question->expects($this->any())
            ->method('ask')
            ->will($this->returnValue('yes_'.uniqid('', true)));

        // We override the standard helper with our mock
        $command->getHelperSet()->set($question, 'question');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertContains('Checking system requirements.', $tester->getDisplay());
        $this->assertContains('Setting up database.', $tester->getDisplay());
        $this->assertContains('Administration setup.', $tester->getDisplay());
        $this->assertContains('Config setup.', $tester->getDisplay());
        $this->assertContains('Installing assets.', $tester->getDisplay());
    }

    public function testRunInstallCommandWithReset()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new InstallCommandMock());

        $command = $application->find('wallabag:install');

        // We mock the QuestionHelper
        $question = $this->getMockBuilder('Symfony\Component\Console\Helper\QuestionHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $question->expects($this->any())
            ->method('ask')
            ->will($this->returnValue('yes_'.uniqid('', true)));

        // We override the standard helper with our mock
        $command->getHelperSet()->set($question, 'question');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            '--reset' => true,
        ]);

        $this->assertContains('Checking system requirements.', $tester->getDisplay());
        $this->assertContains('Setting up database.', $tester->getDisplay());
        $this->assertContains('Droping database, creating database and schema, clearing the cache', $tester->getDisplay());
        $this->assertContains('Administration setup.', $tester->getDisplay());
        $this->assertContains('Config setup.', $tester->getDisplay());
        $this->assertContains('Installing assets.', $tester->getDisplay());

        // we force to reset everything
        $this->assertContains('Droping database, creating database and schema, clearing the cache', $tester->getDisplay());
    }

    public function testRunInstallCommandWithDatabaseRemoved()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new DropDatabaseDoctrineCommand());

        // drop database first, so the install command won't ask to reset things
        $command = $application->find('doctrine:database:drop');
        $command->run(new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force' => true,
        ]), new NullOutput());

        // start a new application to avoid lagging connexion to pgsql
        $client = static::createClient();
        $application = new Application($client->getKernel());
        $application->add(new InstallCommand());

        $command = $application->find('wallabag:install');

        // We mock the QuestionHelper
        $question = $this->getMockBuilder('Symfony\Component\Console\Helper\QuestionHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $question->expects($this->any())
            ->method('ask')
            ->will($this->returnValue('yes_'.uniqid('', true)));

        // We override the standard helper with our mock
        $command->getHelperSet()->set($question, 'question');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertContains('Checking system requirements.', $tester->getDisplay());
        $this->assertContains('Setting up database.', $tester->getDisplay());
        $this->assertContains('Administration setup.', $tester->getDisplay());
        $this->assertContains('Config setup.', $tester->getDisplay());
        $this->assertContains('Installing assets.', $tester->getDisplay());

        // the current database doesn't already exist
        $this->assertContains('Creating database and schema, clearing the cache', $tester->getDisplay());
    }

    public function testRunInstallCommandChooseResetSchema()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new InstallCommandMock());

        $command = $application->find('wallabag:install');

        // We mock the QuestionHelper
        $question = $this->getMockBuilder('Symfony\Component\Console\Helper\QuestionHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $question->expects($this->exactly(3))
            ->method('ask')
            ->will($this->onConsecutiveCalls(
                false, // don't want to reset the entire database
                true, // do want to reset the schema
                false // don't want to create a new user
            ));

        // We override the standard helper with our mock
        $command->getHelperSet()->set($question, 'question');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertContains('Checking system requirements.', $tester->getDisplay());
        $this->assertContains('Setting up database.', $tester->getDisplay());
        $this->assertContains('Administration setup.', $tester->getDisplay());
        $this->assertContains('Config setup.', $tester->getDisplay());
        $this->assertContains('Installing assets.', $tester->getDisplay());

        $this->assertContains('Droping schema and creating schema', $tester->getDisplay());
    }

    public function testRunInstallCommandChooseNothing()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new InstallCommand());
        $application->add(new DropDatabaseDoctrineCommand());
        $application->add(new CreateDatabaseDoctrineCommand());

        // drop database first, so the install command won't ask to reset things
        $command = new DropDatabaseDoctrineCommand();
        $command->setApplication($application);
        $command->run(new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force' => true,
        ]), new NullOutput());

        $this->getClient()->getContainer()->get('doctrine')->getConnection()->close();

        $command = new CreateDatabaseDoctrineCommand();
        $command->setApplication($application);
        $command->run(new ArrayInput([
            'command' => 'doctrine:database:create',
            '--env' => 'test',
        ]), new NullOutput());

        $command = $application->find('wallabag:install');

        // We mock the QuestionHelper
        $question = $this->getMockBuilder('Symfony\Component\Console\Helper\QuestionHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $question->expects($this->exactly(2))
            ->method('ask')
            ->will($this->onConsecutiveCalls(
                false, // don't want to reset the entire database
                false // don't want to create a new user
            ));

        // We override the standard helper with our mock
        $command->getHelperSet()->set($question, 'question');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertContains('Checking system requirements.', $tester->getDisplay());
        $this->assertContains('Setting up database.', $tester->getDisplay());
        $this->assertContains('Administration setup.', $tester->getDisplay());
        $this->assertContains('Config setup.', $tester->getDisplay());
        $this->assertContains('Installing assets.', $tester->getDisplay());

        $this->assertContains('Creating schema', $tester->getDisplay());
    }

    public function testRunInstallCommandNoInteraction()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new InstallCommandMock());

        $command = $application->find('wallabag:install');

        // We mock the QuestionHelper
        $question = $this->getMockBuilder('Symfony\Component\Console\Helper\QuestionHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $question->expects($this->any())
            ->method('ask')
            ->will($this->returnValue('yes_'.uniqid('', true)));

        // We override the standard helper with our mock
        $command->getHelperSet()->set($question, 'question');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            '--no-interaction' => true,
        ]);

        $this->assertContains('Checking system requirements.', $tester->getDisplay());
        $this->assertContains('Setting up database.', $tester->getDisplay());
        $this->assertContains('Administration setup.', $tester->getDisplay());
        $this->assertContains('Config setup.', $tester->getDisplay());
        $this->assertContains('Installing assets.', $tester->getDisplay());
    }
}
