<?php

namespace App\Tests\Command;

use App\Command\InstallCommand;
use App\Tests\WallabagCoreTestCase;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;

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

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Connection $connection */
        $connection = $this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection();
        if ($connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
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

        $this->resetDatabase($this->getTestClient());
    }

    protected function tearDown(): void
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
        $application = new Application($this->getTestClient()->getKernel());

        /** @var InstallCommand $command */
        $command = $application->find('wallabag:install');

        // enable calling other commands for MySQL only because rollback isn't supported
        if (!$this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof MySQLPlatform) {
            $command->disableRunOtherCommands();
        }

        $tester = new CommandTester($command);
        $tester->setInputs([
            'y', // dropping database
            'y', // create super admin
            'username_' . uniqid('', true), // username
            'password_' . uniqid('', true), // password
            'email_' . uniqid('', true) . '@wallabag.it', // email
        ]);
        $tester->execute([]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());
    }

    public function testRunInstallCommandWithReset()
    {
        if ($this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->markTestSkipped('Rollback are not properly handled for MySQL, skipping.');
        }

        $application = new Application($this->getTestClient()->getKernel());

        /** @var InstallCommand $command */
        $command = $application->find('wallabag:install');
        $command->disableRunOtherCommands();

        $tester = new CommandTester($command);
        $tester->setInputs([
            'y', // create super admin
            'username_' . uniqid('', true), // username
            'password_' . uniqid('', true), // password
            'email_' . uniqid('', true) . '@wallabag.it', // email
        ]);
        $tester->execute([
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
        if ($this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->markTestSkipped('Rollback are not properly handled for MySQL, skipping.');
        }

        // skipped SQLite check when database is removed because while testing for the connection,
        // the driver will create the file (so the database) before testing if database exist
        if ($this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof SqlitePlatform) {
            $this->markTestSkipped('SQLite spotted: can\'t test with database removed.');
        }

        $application = new Application($this->getTestClient()->getKernel());

        // drop database first, so the install command won't ask to reset things
        $command = $application->find('doctrine:database:drop');
        $command->run(new ArrayInput([
            '--force' => true,
        ]), new NullOutput());

        // start a new application to avoid lagging connexion to pgsql
        $client = static::createClient();
        $application = new Application($client->getKernel());

        $command = $application->find('wallabag:install');

        $tester = new CommandTester($command);
        $tester->setInputs([
            'y', // create super admin
            'username_' . uniqid('', true), // username
            'password_' . uniqid('', true), // password
            'email_' . uniqid('', true) . '@wallabag.it', // email
        ]);
        $tester->execute([]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());

        // the current database doesn't already exist
        $this->assertStringContainsString('Creating database and schema, clearing the cache', $tester->getDisplay());
    }

    public function testRunInstallCommandChooseResetSchema()
    {
        if ($this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->markTestSkipped('Rollback are not properly handled for MySQL, skipping.');
        }

        $application = new Application($this->getTestClient()->getKernel());

        /** @var InstallCommand $command */
        $command = $application->find('wallabag:install');
        $command->disableRunOtherCommands();

        $tester = new CommandTester($command);
        $tester->setInputs([
            'n', // don't want to reset the entire database
            'y', // do want to reset the schema
            'n', // don't want to create a new user
        ]);
        $tester->execute([]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());

        $this->assertStringContainsString('Dropping schema and creating schema', $tester->getDisplay());
    }

    public function testRunInstallCommandChooseNothing()
    {
        /*
         *  [PHPUnit\Framework\Error\Warning (2)]
         *  filemtime(): stat failed for /home/runner/work/wallabag/wallabag/var/cache/tes_/ContainerNVNxA24/appAppKernelTestDebugContainer.php
         *
         * I don't know from where the "/tes_/" come from, it should be "/test/" instead ...
         */
        if ($this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->markTestSkipped('That test is failing when using MySQL when clearing the cache (see code comment)');
        }

        $application = new Application($this->getTestClient()->getKernel());

        // drop database first, so the install command won't ask to reset things
        $command = $application->find('doctrine:database:drop');
        $command->run(new ArrayInput([
            '--force' => true,
        ]), new NullOutput());

        $this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->close();

        $command = $application->find('doctrine:database:create');
        $command->run(new ArrayInput([]), new NullOutput());

        $command = $application->find('wallabag:install');

        $tester = new CommandTester($command);
        $tester->setInputs([
            'n', // don't want to reset the entire database
            'n', // don't want to create a new user
        ]);
        $tester->execute([]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());

        $this->assertStringContainsString('Creating schema', $tester->getDisplay());
    }

    public function testRunInstallCommandNoInteraction()
    {
        if ($this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->markTestSkipped('Rollback are not properly handled for MySQL, skipping.');
        }

        $application = new Application($this->getTestClient()->getKernel());

        /** @var InstallCommand $command */
        $command = $application->find('wallabag:install');
        $command->disableRunOtherCommands();

        $tester = new CommandTester($command);
        $tester->execute([], [
            'interactive' => false,
        ]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());
    }
}
