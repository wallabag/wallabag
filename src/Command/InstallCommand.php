<?php

namespace Wallabag\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wallabag\Entity\IgnoreOriginInstanceRule;
use Wallabag\Entity\InternalSetting;
use Wallabag\Entity\User;

class InstallCommand extends Command
{
    protected static $defaultName = 'wallabag:install';
    protected static $defaultDescription = 'wallabag installer.';

    private InputInterface $defaultInput;
    private SymfonyStyle $io;
    private array $functionExists = [
        'curl_exec',
        'curl_multi_init',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly UserManagerInterface $userManager,
        private readonly TableMetadataStorageConfiguration $tableMetadataStorageConfiguration,
        private readonly string $databaseDriver,
        private readonly array $defaultSettings,
        private readonly array $defaultIgnoreOriginInstanceRules,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption(
                'reset',
                null,
                InputOption::VALUE_NONE,
                'Reset current database'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->defaultInput = $input;

        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('wallabag installer');

        $this
            ->checkRequirements()
            ->setupDatabase()
            ->setupAdmin()
            ->setupConfig()
        ;

        $this->io->success('wallabag has been successfully installed.');
        $this->io->success('You can now configure your web server, see https://doc.wallabag.org');

        return 0;
    }

    private function checkRequirements()
    {
        $this->io->section('Step 1 of 4: Checking system requirements.');

        $rows = [];

        // testing if database driver exists
        $fulfilled = true;
        $label = '<comment>PDO Driver (%s)</comment>';
        $status = '<info>OK!</info>';
        $help = '';

        if (!\extension_loaded($this->databaseDriver)) {
            $fulfilled = false;
            $status = '<error>ERROR!</error>';
            $help = 'Database driver "' . $this->databaseDriver . '" is not installed.';
        }

        $rows[] = [\sprintf($label, $this->databaseDriver), $status, $help];

        // testing if connection to the database can be established
        $label = '<comment>Database connection</comment>';
        $status = '<info>OK!</info>';
        $help = '';

        $conn = $this->entityManager->getConnection();

        try {
            $conn->connect();
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), 'Unknown database')
                && !str_contains($e->getMessage(), 'database "' . $conn->getDatabase() . '" does not exist')) {
                $fulfilled = false;
                $status = '<error>ERROR!</error>';
                $help = 'Can\'t connect to the database: ' . $e->getMessage();
            }
        }

        $rows[] = [$label, $status, $help];

        // check MySQL & PostgreSQL version
        $label = '<comment>Database version</comment>';
        $status = '<info>OK!</info>';
        $help = '';

        // now check if MySQL isn't too old to handle utf8mb4
        if ($conn->isConnected() && $conn->getDatabasePlatform() instanceof MySQLPlatform) {
            $version = $conn->executeQuery('select version()')->fetchOne();
            $minimalVersion = '5.5.4';

            if (false === version_compare($version, $minimalVersion, '>')) {
                $fulfilled = false;
                $status = '<error>ERROR!</error>';
                $help = 'Your MySQL version (' . $version . ') is too old, consider upgrading (' . $minimalVersion . '+).';
            }
        }

        // testing if PostgreSQL > 9.1
        if ($conn->isConnected() && $conn->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            // return version should be like "PostgreSQL 9.5.4 on x86_64-apple-darwin15.6.0, compiled by Apple LLVM version 8.0.0 (clang-800.0.38), 64-bit"
            $version = $conn->executeQuery('SELECT version();')->fetchOne();

            preg_match('/PostgreSQL ([0-9\.]+)/i', (string) $version, $matches);

            if (isset($matches[1]) & version_compare($matches[1], '9.2.0', '<')) {
                $fulfilled = false;
                $status = '<error>ERROR!</error>';
                $help = 'PostgreSQL should be greater than 9.1 (actual version: ' . $matches[1] . ')';
            }
        }

        $rows[] = [$label, $status, $help];

        foreach ($this->functionExists as $functionRequired) {
            $label = '<comment>' . $functionRequired . '</comment>';
            $status = '<info>OK!</info>';
            $help = '';

            if (!\function_exists($functionRequired)) {
                $fulfilled = false;
                $status = '<error>ERROR!</error>';
                $help = 'You need the ' . $functionRequired . ' function activated';
            }

            $rows[] = [$label, $status, $help];
        }

        $this->io->table(['Checked', 'Status', 'Recommendation'], $rows);

        if (!$fulfilled) {
            throw new \RuntimeException('Some system requirements are not fulfilled. Please check output messages and fix them.');
        }

        $this->io->success('Success! Your system can run wallabag properly.');

        return $this;
    }

    private function setupDatabase()
    {
        $this->io->section('Step 2 of 4: Setting up database.');

        $conn = $this->entityManager->getConnection();
        $databasePlatform = $conn->isConnected() ? $conn->getDatabasePlatform() : null;

        // user want to reset everything? Don't care about what is already here
        if (true === $this->defaultInput->getOption('reset')) {
            $this->io->text('Dropping database, creating database and schema, clearing the cache');

            $this->runCommand('doctrine:schema:drop', ['--force' => true, '--full-database' => true]);

            if (!$databasePlatform instanceof PostgreSQLPlatform) {
                $this->runCommand('doctrine:database:drop', ['--force' => true]);
                $this->runCommand('doctrine:database:create');
            }

            $this
                ->runCommand('doctrine:migrations:migrate', ['--no-interaction' => true])
                ->runCommand('cache:clear')
            ;

            $this->io->newLine();

            return $this;
        }

        if (!$this->isDatabasePresent()) {
            $this->io->text('Creating database and schema, clearing the cache');

            $this
                ->runCommand('doctrine:database:create')
                ->runCommand('doctrine:migrations:migrate', ['--no-interaction' => true])
                ->runCommand('cache:clear')
            ;

            $this->io->newLine();

            return $this;
        }

        if ($this->io->confirm('It appears that your database already exists. Would you like to reset it?', false)) {
            $this->io->text('Dropping database, creating database and schema...');

            $this->runCommand('doctrine:schema:drop', ['--force' => true, '--full-database' => true]);

            if (!$databasePlatform instanceof PostgreSQLPlatform) {
                $this->runCommand('doctrine:database:drop', ['--force' => true]);
                $this->runCommand('doctrine:database:create');
            }

            $this->runCommand('doctrine:migrations:migrate', ['--no-interaction' => true]);
        } elseif ($this->isSchemaPresent()) {
            if ($this->io->confirm('Seems like your database contains schema. Do you want to reset it?', false)) {
                $this->io->text('Dropping schema and creating schema...');

                $this->dropWallabagSchemaOnly();
                $this->runCommand('doctrine:migrations:migrate', ['--no-interaction' => true]);
            }
        } else {
            $this->io->text('Creating schema...');

            $this
                ->runCommand('doctrine:migrations:migrate', ['--no-interaction' => true])
            ;
        }

        $this->io->text('Clearing the cache...');
        $this->runCommand('cache:clear');

        $this->io->newLine();
        $this->io->text('<info>Database successfully setup.</info>');

        return $this;
    }

    private function setupAdmin()
    {
        $this->io->section('Step 3 of 4: Administration setup.');

        if (!$this->io->confirm('Would you like to create a new admin user (recommended)?', true)) {
            return $this;
        }

        $user = $this->userManager->createUser();
        \assert($user instanceof User);

        $user->setUsername($this->io->ask('Username', 'wallabag'));

        $question = new Question('Password', 'wallabag');
        $question->setHidden(true);
        $user->setPlainPassword($this->io->askQuestion($question));

        $user->setEmail($this->io->ask('Email', 'wallabag@wallabag.io'));

        $user->setEnabled(true);
        $user->addRole('ROLE_SUPER_ADMIN');

        $this->entityManager->persist($user);

        // dispatch a created event so the associated config will be created
        $this->dispatcher->dispatch(new UserEvent($user), FOSUserEvents::USER_CREATED);

        $this->io->text('<info>Administration successfully setup.</info>');

        return $this;
    }

    private function setupConfig()
    {
        $this->io->section('Step 4 of 4: Config setup.');

        // cleanup before insert new stuff
        $this->entityManager->createQuery('DELETE FROM Wallabag\Entity\InternalSetting')->execute();
        $this->entityManager->createQuery('DELETE FROM Wallabag\Entity\IgnoreOriginInstanceRule')->execute();

        foreach ($this->defaultSettings as $setting) {
            $newSetting = new InternalSetting();
            $newSetting->setName($setting['name']);
            $newSetting->setValue($setting['value']);
            $newSetting->setSection($setting['section']);

            $this->entityManager->persist($newSetting);
        }

        foreach ($this->defaultIgnoreOriginInstanceRules as $ignore_origin_instance_rule) {
            $newIgnoreOriginInstanceRule = new IgnoreOriginInstanceRule();
            $newIgnoreOriginInstanceRule->setRule($ignore_origin_instance_rule['rule']);

            $this->entityManager->persist($newIgnoreOriginInstanceRule);
        }

        $this->entityManager->flush();

        $this->io->text('<info>Config successfully setup.</info>');

        return $this;
    }

    /**
     * Run a command.
     *
     * @param string $command
     * @param array  $parameters Parameters to this command (usually 'force' => true)
     */
    private function runCommand($command, $parameters = [])
    {
        $parameters = array_merge(
            ['command' => $command],
            $parameters,
            [
                '--no-debug' => true,
                '--env' => $this->defaultInput->getOption('env') ?: 'dev',
            ]
        );

        if ($this->defaultInput->getOption('no-interaction')) {
            $parameters = array_merge($parameters, ['--no-interaction' => true]);
        }

        $this->getApplication()->setAutoExit(false);

        $output = new BufferedOutput();
        $exitCode = $this->getApplication()->run(new ArrayInput($parameters), $output);

        // PDO does not always close the connection after Doctrine commands.
        // See https://github.com/symfony/symfony/issues/11750.
        $this->entityManager->getConnection()->close();

        if (0 !== $exitCode) {
            $this->getApplication()->setAutoExit(true);

            throw new \RuntimeException('The command "' . $command . "\" generates some errors: \n\n" . $output->fetch());
        }

        return $this;
    }

    /**
     * Check if the database already exists.
     *
     * @return bool
     */
    private function isDatabasePresent()
    {
        $connection = $this->entityManager->getConnection();
        $params = $connection->getParams();

        if ($connection->getDatabasePlatform() instanceof SqlitePlatform) {
            $databaseName = $params['path'];
        } else {
            $databaseName = $params['dbname'];
        }

        try {
            $schemaManager = $connection->createSchemaManager();
        } catch (\Exception $exception) {
            // mysql & sqlite
            if (str_contains($exception->getMessage(), \sprintf("Unknown database '%s'", $databaseName))) {
                return false;
            }

            // pgsql
            if (str_contains($exception->getMessage(), \sprintf('database "%s" does not exist', $databaseName))) {
                return false;
            }

            throw $exception;
        }

        // custom verification for sqlite, since `getListDatabasesSQL` doesn't work for sqlite
        if ($connection->getDatabasePlatform() instanceof SqlitePlatform) {
            if (isset($params['path']) && file_exists($params['path'])) {
                return true;
            }

            return false;
        }

        try {
            return \in_array($databaseName, $schemaManager->listDatabases(), true);
        } catch (DriverException) {
            // it means we weren't able to get database list, assume the database doesn't exist

            return false;
        }
    }

    /**
     * Check if the schema is already created.
     * We use the Doctrine Migrations table for the check.
     */
    private function isSchemaPresent(): bool
    {
        $schemaManager = $this->entityManager->getConnection()->createSchemaManager();

        return $schemaManager->tablesExist([$this->tableMetadataStorageConfiguration->getTableName()]);
    }

    private function dropWallabagSchemaOnly(): void
    {
        $this->runCommand('doctrine:schema:drop', ['--force' => true]);

        $connection = $this->entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        $connection->executeQuery('DROP TABLE ' . $databasePlatform->quoteIdentifier($this->tableMetadataStorageConfiguration->getTableName()) . ';');
    }
}
