<?php

namespace Wallabag\Tests\Integration\Command;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\Persistence\ManagerRegistry;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\Console\Command\LazyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Command\InstallCommand;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class InstallCommandTest extends WallabagKernelTestCase
{
    private ?string $initialDatabaseUrl = null;

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
        $connection = static::getContainer()->get(ManagerRegistry::class)->getConnection();

        // Capture the initial DATABASE_URL before modifying it.
        // Read from $_ENV because Symfony's EnvVarProcessor checks $_ENV before getenv(),
        // and Dotenv::bootEnv() (usePutenv=false by default) only sets $_ENV / $_SERVER.
        $this->initialDatabaseUrl = $_ENV['DATABASE_URL'] ?? null;

        $originalDatabaseUrl = $this->initialDatabaseUrl
            ?? $_SERVER['DATABASE_URL']
            ?? getenv('DATABASE_URL');

        $tmpDatabaseName = 'wallabag_' . bin2hex(random_bytes(5));

        if ($connection->getDatabasePlatform() instanceof SqlitePlatform) {
            $dbFilename = basename($connection->getParams()['path']);
            $tmpFilename = $tmpDatabaseName . '.sqlite';
            $tmpDatabaseUrl = str_replace($dbFilename, $tmpFilename, $originalDatabaseUrl);
        } else {
            $tmpDatabaseUrl = (string) (new Uri($originalDatabaseUrl))->withPath($tmpDatabaseName);
        }

        // Update all three env channels so Symfony's EnvVarProcessor picks up the change.
        putenv("DATABASE_URL=$tmpDatabaseUrl");
        $_ENV['DATABASE_URL'] = $tmpDatabaseUrl;
        $_SERVER['DATABASE_URL'] = $tmpDatabaseUrl;

        if ($connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            // PostgreSQL requires that the database exists before connecting to it
            $connection->executeQuery('CREATE DATABASE ' . $tmpDatabaseName);
        }

        // The environment has been changed, reboot the kernel to update the connection.
        $this->rebootKernel();
    }

    protected function tearDown(): void
    {
        $databaseUrl = getenv('DATABASE_URL');

        /** @var Connection $connection */
        $connection = static::getContainer()->get(ManagerRegistry::class)->getConnection();

        if ($connection->getDatabasePlatform() instanceof SqlitePlatform) {
            // Restore all env channels to their initial state.
            if (null !== $this->initialDatabaseUrl) {
                putenv("DATABASE_URL={$this->initialDatabaseUrl}");
                $_ENV['DATABASE_URL'] = $this->initialDatabaseUrl;
                $_SERVER['DATABASE_URL'] = $this->initialDatabaseUrl;
            } else {
                putenv('DATABASE_URL');
                unset($_ENV['DATABASE_URL'], $_SERVER['DATABASE_URL']);
            }

            $databasePath = parse_url($databaseUrl, \PHP_URL_PATH);

            if (file_exists($databasePath)) {
                unlink($databasePath);
            }
        } else {
            $testDatabaseName = $connection->getDatabase();
            $connection->close();

            // Restore all env channels so the rebooted kernel connects to the original database,
            // which is required to be able to DROP the temporary database.
            if (null !== $this->initialDatabaseUrl) {
                putenv("DATABASE_URL={$this->initialDatabaseUrl}");
                $_ENV['DATABASE_URL'] = $this->initialDatabaseUrl;
                $_SERVER['DATABASE_URL'] = $this->initialDatabaseUrl;
            } else {
                putenv('DATABASE_URL');
                unset($_ENV['DATABASE_URL'], $_SERVER['DATABASE_URL']);
            }

            // Reboot the kernel to avoid the rollback-only transaction state.
            $this->rebootKernel();

            static::getContainer()->get(ManagerRegistry::class)->getConnection()->executeQuery('DROP DATABASE ' . $testDatabaseName);
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
        if (static::getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->markTestSkipped('PostgreSQL spotted: PostgreSQL requires that the database exists before connecting to it, skipping.');
        }

        // skipped SQLite check when database is removed because while testing for the connection,
        // the driver will create the file (so the database) before testing if database exist
        if (static::getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof SqlitePlatform) {
            $this->markTestSkipped('SQLite spotted: can\'t test with database removed.');
        }

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

        $databasePlatform = static::getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform();

        if ($databasePlatform instanceof SqlitePlatform || $databasePlatform instanceof PostgreSQLPlatform) {
            // SQLite and PostgreSQL always have the database created, so we create the schema only
            $this->assertStringContainsString('Creating schema', $tester->getDisplay());
        }

        if ($databasePlatform instanceof MySQLPlatform) {
            // MySQL can start with a non-existing database, so we create both the database and the schema
            $this->assertStringContainsString('Creating database and schema', $tester->getDisplay());
        }
    }

    public function testRunInstallCommandSchemaExistsNotReset()
    {
        $this->setupDatabase();

        $command = $this->getCommand();

        $tester = new CommandTester($command);
        $tester->setInputs([
            'n', // don't want to reset the entire database
            'n', // don't want to reset the schema
            // no further input — admin prompt must NOT appear
        ]);
        $tester->execute([]);

        $this->assertStringContainsString('Setting up database.', $tester->getDisplay());
        $this->assertStringContainsString('Administration setup.', $tester->getDisplay());
        $this->assertStringContainsString('Existing schema kept', $tester->getDisplay());
        $this->assertStringNotContainsString('Would you like to create a new admin user', $tester->getDisplay());
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
        $application = $this->createApplication();

        $command = $application->find('wallabag:install');

        if ($command instanceof LazyCommand) {
            $command = $command->getCommand();
        }

        \assert($command instanceof InstallCommand);

        return $command;
    }

    private function setupDatabase()
    {
        $application = $this->createApplication();
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

        // Reboot the kernel to avoid the rollback-only transaction state.
        $this->rebootKernel();
    }
}
