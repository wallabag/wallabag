<?php

namespace Tests\Wallabag\Command;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\Persistence\ManagerRegistry;
use GuzzleHttp\Psr7\Uri;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\LazyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Command\InstallCommand;

class InstallCommandTest extends WallabagTestCase
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

        $originalDatabaseUrl = $this->getTestClient()->getContainer()->getParameter('env(DATABASE_URL)');
        $dbnameSuffix = $this->getTestClient()->getContainer()->getParameter('wallabag_dbname_suffix');
        $tmpDatabaseName = 'wallabag_' . bin2hex(random_bytes(5));

        if ($connection->getDatabasePlatform() instanceof SqlitePlatform) {
            $tmpDatabaseUrl = str_replace('wallabag' . $dbnameSuffix . '.sqlite', $tmpDatabaseName . $dbnameSuffix . '.sqlite', $originalDatabaseUrl);
        } else {
            $tmpDatabaseUrl = (string) (new Uri($originalDatabaseUrl))->withPath($tmpDatabaseName);
        }

        putenv("DATABASE_URL=$tmpDatabaseUrl");

        if ($connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            // PostgreSQL requires that the database exists before connecting to it
            $tmpTestDatabaseName = $tmpDatabaseName . $dbnameSuffix;
            $connection->executeQuery('CREATE DATABASE ' . $tmpTestDatabaseName);
        }

        // The environnement has been changed, recreate the client in order to update connection
        $this->getNewClient();
    }

    protected function tearDown(): void
    {
        $databaseUrl = getenv('DATABASE_URL');

        /** @var Connection $connection */
        $connection = $this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection();

        if ($connection->getDatabasePlatform() instanceof SqlitePlatform) {
            // Remove the real environnement variable
            putenv('DATABASE_URL');

            $databasePath = parse_url($databaseUrl, \PHP_URL_PATH);

            if (file_exists($databasePath)) {
                unlink($databasePath);
            }
        } else {
            $testDatabaseName = $connection->getDatabase();
            $connection->close();

            // Remove the real environnement variable
            putenv('DATABASE_URL');

            // Create a new client to avoid the error:
            // Transaction commit failed because the transaction has been marked for rollback only.
            $this->getNewClient();

            $this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->executeQuery('DROP DATABASE ' . $testDatabaseName);
        }

        parent::tearDown();
    }

    public function testRunInstallCommand()
    {
        $this->setupDatabase();

        $command = $this->getCommand();

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
        $this->setupDatabase();

        $command = $this->getCommand();

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

    public function testRunInstallCommandWithNonExistingDatabase()
    {
        if ($this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->markTestSkipped('PostgreSQL spotted: PostgreSQL requires that the database exists before connecting to it, skipping.');
        }

        // skipped SQLite check when database is removed because while testing for the connection,
        // the driver will create the file (so the database) before testing if database exist
        if ($this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof SqlitePlatform) {
            $this->markTestSkipped('SQLite spotted: can\'t test with database removed.');
        }

        $application = new Application($this->getTestClient()->getKernel());

        $command = $this->getCommand();

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
        $this->setupDatabase();

        $command = $this->getCommand();

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
        $application = new Application($this->getTestClient()->getKernel());

        $command = $this->getCommand();

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

        $databasePlatform = $this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform();

        if ($databasePlatform instanceof SqlitePlatform || $databasePlatform instanceof PostgreSQLPlatform) {
            // SQLite and PostgreSQL always have the database created, so we create the schema only
            $this->assertStringContainsString('Creating schema', $tester->getDisplay());
        }

        if ($databasePlatform instanceof MySQLPlatform) {
            // MySQL can start with a non-existing database, so we create both the database and the schema
            $this->assertStringContainsString('Creating database and schema', $tester->getDisplay());
        }
    }

    public function testRunInstallCommandNoInteraction()
    {
        $this->setupDatabase();

        $command = $this->getCommand();

        $tester = new CommandTester($command);
        $tester->execute([], [
            'interactive' => false,
        ]);

        $this->assertStringContainsString('Checking system requirements.', $tester->getDisplay());
        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Config setup.', $tester->getDisplay());
    }

    private function getCommand(): InstallCommand
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:install');

        if ($command instanceof LazyCommand) {
            $command = $command->getCommand();
        }

        \assert($command instanceof InstallCommand);

        return $command;
    }

    private function setupDatabase()
    {
        $application = new Application($this->getTestClient()->getKernel());
        $application->setAutoExit(false);

        $application->run(new ArrayInput([
            'command' => 'doctrine:database:create',
            '--no-interaction' => true,
            '--env' => 'test',
        ]), new NullOutput());

        $application->run(new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
            '--env' => 'test',
        ]), new NullOutput());

        $application->run(new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true,
            '--env' => 'test',
        ]), new NullOutput());

        /*
         * Recreate client to avoid error:
         *
         * [Doctrine\DBAL\ConnectionException]
         * Transaction commit failed because the transaction has been marked for rollback only.
         */
        $this->getNewClient();
    }
}
