<?php

namespace Tests\Wallabag\Command;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\Persistence\ManagerRegistry;
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

        if ($connection->getDatabasePlatform() instanceof SqlitePlatform) {
            // Environnement variable useful only for sqlite to avoid the error "attempt to write a readonly database"
            // We can't define always this environnement variable because pdo_mysql seems to use it
            // and we have the error:
            // SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax;
            // check the manual that corresponds to your MariaDB server version for the right syntax to use
            // near '/tmp/wallabag_testTYj1kp' at line 1
            $databasePath = tempnam(sys_get_temp_dir(), 'wallabag_test');
            putenv("DATABASE_URL=sqlite:///$databasePath?charset=utf8");

            // The environnement has been changed, recreate the client in order to update connection
            $this->getNewClient();
        }

        $this->resetDatabase($this->getTestClient());
    }

    protected function tearDown(): void
    {
        $databaseUrl = getenv('DATABASE_URL');
        $databasePath = parse_url($databaseUrl, \PHP_URL_PATH);
        // Remove the real environnement variable
        putenv('DATABASE_URL');

        if ($databasePath && file_exists($databasePath)) {
            unlink($databasePath);
        } else {
            // Create a new client to avoid the error:
            // Transaction commit failed because the transaction has been marked for rollback only.
            $client = $this->getNewClient();
            $this->resetDatabase($client);
        }

        parent::tearDown();
    }

    public function testRunInstallCommand()
    {
        $command = $this->getCommand();

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

        $command = $this->getCommand();
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
        if ($this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->markTestSkipped('PostgreSQL spotted: can\'t find a good way to drop current database, skipping.');
        }

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
        $this->getNewClient();

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
        if ($this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->markTestSkipped('Rollback are not properly handled for MySQL, skipping.');
        }

        $command = $this->getCommand();
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

        $this->assertStringContainsString('Creating schema', $tester->getDisplay());
    }

    public function testRunInstallCommandNoInteraction()
    {
        if ($this->getTestClient()->getContainer()->get(ManagerRegistry::class)->getConnection()->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->markTestSkipped('Rollback are not properly handled for MySQL, skipping.');
        }

        $command = $this->getCommand();
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
}
