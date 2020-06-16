<?php

namespace Tests\Wallabag\CoreBundle\Command;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\MigrationsBundle\Command\MigrationsMigrateDoctrineCommand;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\Mock\InstallCommandMock;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Command\InstallCommand;

class InstallCommandTest extends WallabagCoreTestCase
{
    public static function setUpBeforeClass(): void
    {
        // disable doctrine-test-bundle
        StaticDriver::setKeepStaticConnections(false);
    }

    public static function tearDownAfterClass(): void
    {
        // enable doctrine-test-bundle
        StaticDriver::setKeepStaticConnections(true);
    }

    public function setUp(): void
    {
        parent::setUp();

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->getClient()->getContainer()->get('doctrine')->getConnection();
        if ($connection->getDatabasePlatform() instanceof PostgreSqlPlatform) {
            /*
             * LOG:  statement: CREATE DATABASE "wallabag"
             * ERROR:  source database "template1" is being accessed by other users
             * DETAIL:  There is 1 other session using the database.
             * STATEMENT:  CREATE DATABASE "wallabag"
             * FATAL:  database "wallabag" does not exist
             *
             * http://stackoverflow.com/a/14374832/569101
             */
            $this->markTestSkipped('PostgreSQL spotted: can\'t find a good way to drop current database, skipping.');
        }

        if ($connection->getDatabasePlatform() instanceof SqlitePlatform) {
            // Environnement variable useful only for sqlite to avoid the error "attempt to write a readonly database"
            // We can't define always this environnement variable because pdo_mysql seems to use it
            // and we have the error:
            // SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax;
            // check the manual that corresponds to your MariaDB server version for the right syntax to use
            // near '/tmp/wallabag_testTYj1kp' at line 1
            $databasePath = tempnam(sys_get_temp_dir(), 'wallabag_test');
            putenv("TEST_DATABASE_PATH=$databasePath");

            // The environnement has been changed, recreate the client in order to update connection
            parent::setUp();
        }

        $this->resetDatabase($this->getClient());
    }

    public function tearDown(): void
    {
        $databasePath = getenv('TEST_DATABASE_PATH');
        // Remove variable environnement
        putenv('TEST_DATABASE_PATH');

        if ($databasePath && file_exists($databasePath)) {
            unlink($databasePath);
        } else {
            // Create a new client to avoid the error:
            // Transaction commit failed because the transaction has been marked for rollback only.
            $client = static::createClient();
            $this->resetDatabase($client);
        }

        parent::tearDown();
    }

    public function testRunInstallCommand()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new InstallCommandMock());

        $command = $application->find('wallabag:install');

        $tester = new CommandTester($command);
        $tester->setInputs([
            'y', // dropping database
            'y', // create super admin
            'username_' . uniqid('', true), // username
            'password_' . uniqid('', true), // password
            'email_' . uniqid('', true) . '@wallabag.it', // email
        ]);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());
    }

    public function testRunInstallCommandWithReset()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new InstallCommandMock());

        $command = $application->find('wallabag:install');

        $tester = new CommandTester($command);
        $tester->setInputs([
            'y', // create super admin
            'username_' . uniqid('', true), // username
            'password_' . uniqid('', true), // password
            'email_' . uniqid('', true) . '@wallabag.it', // email
        ]);
        $tester->execute([
            'command' => $command->getName(),
            '--reset' => true,
        ]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Dropping database, creating database and schema, clearing the cache', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());

        // we force to reset everything
        $this->assertStringContainsString('Dropping database, creating database and schema, clearing the cache', $tester->getDisplay());
    }

    public function testRunInstallCommandWithDatabaseRemoved()
    {
        // skipped SQLite check when database is removed because while testing for the connection,
        // the driver will create the file (so the database) before testing if database exist
        if ($this->getClient()->getContainer()->get('doctrine')->getConnection()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform) {
            $this->markTestSkipped('SQLite spotted: can\'t test with database removed.');
        }

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

        $tester = new CommandTester($command);
        $tester->setInputs([
            'y', // create super admin
            'username_' . uniqid('', true), // username
            'password_' . uniqid('', true), // password
            'email_' . uniqid('', true) . '@wallabag.it', // email
        ]);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());

        // the current database doesn't already exist
        $this->assertStringContainsString('Creating database and schema, clearing the cache', $tester->getDisplay());
    }

    public function testRunInstallCommandChooseResetSchema()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new InstallCommandMock());

        $command = $application->find('wallabag:install');

        $tester = new CommandTester($command);
        $tester->setInputs([
            'n', // don't want to reset the entire database
            'y', // do want to reset the schema
            'n', // don't want to create a new user
        ]);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());

        $this->assertStringContainsString('Dropping schema and creating schema', $tester->getDisplay());
    }

    public function testRunInstallCommandChooseNothing()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new InstallCommand());
        $application->add(new DropDatabaseDoctrineCommand());
        $application->add(new CreateDatabaseDoctrineCommand());
        $application->add(new MigrationsMigrateDoctrineCommand());

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

        $tester = new CommandTester($command);
        $tester->setInputs([
            'n', // don't want to reset the entire database
            'n', // don't want to create a new user
        ]);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());

        $this->assertStringContainsString('Creating schema', $tester->getDisplay());
    }

    public function testRunInstallCommandNoInteraction()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new InstallCommandMock());

        $command = $application->find('wallabag:install');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ], [
            'interactive' => false,
        ]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());
    }
}
