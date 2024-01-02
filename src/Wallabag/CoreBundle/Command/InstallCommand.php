<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
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
use Wallabag\CoreBundle\Entity\IgnoreOriginInstanceRule;
use Wallabag\CoreBundle\Entity\InternalSetting;
use Wallabag\UserBundle\Entity\User;

class InstallCommand extends Command
{
    private InputInterface $defaultInput;
    private SymfonyStyle $io;
    private array $functionExists = [
        'curl_exec',
        'curl_multi_init',
    ];
    private bool $runOtherCommands = true;

    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $dispatcher;
    private UserManagerInterface $userManager;
    private string $databaseDriver;
    private string $databaseName;
    private array $defaultSettings;
    private array $defaultIgnoreOriginInstanceRules;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher, UserManagerInterface $userManager, string $databaseDriver, string $databaseName, array $defaultSettings, array $defaultIgnoreOriginInstanceRules)
    {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->userManager = $userManager;
        $this->databaseDriver = $databaseDriver;
        $this->databaseName = $databaseName;
        $this->defaultSettings = $defaultSettings;
        $this->defaultIgnoreOriginInstanceRules = $defaultIgnoreOriginInstanceRules;

        parent::__construct();
    }

    public function disableRunOtherCommands(): void
    {
        $this->runOtherCommands = false;
    }

    protected function configure()
    {
        $this
            ->setName('wallabag:install')
            ->setDescription('wallabag installer.')
            ->addOption(
               'reset',
               null,
               InputOption::VALUE_NONE,
               'Reset current database'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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

        $rows[] = [sprintf($label, $this->databaseDriver), $status, $help];

        // testing if connection to the database can be established
        $label = '<comment>Database connection</comment>';
        $status = '<info>OK!</info>';
        $help = '';

        $conn = $this->entityManager->getConnection();

        try {
            $conn->connect();
        } catch (\Exception $e) {
            if (false === strpos($e->getMessage(), 'Unknown database')
                && false === strpos($e->getMessage(), 'database "' . $this->databaseName . '" does not exist')) {
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
            $version = $conn->query('select version()')->fetchOne();
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
            $version = $conn->query('SELECT version();')->fetchOne();

            preg_match('/PostgreSQL ([0-9\.]+)/i', $version, $matches);

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

        // user want to reset everything? Don't care about what is already here
        if (true === $this->defaultInput->getOption('reset')) {
            $this->io->text('Dropping database, creating database and schema, clearing the cache');

            $this
                ->runCommand('doctrine:database:drop', ['--force' => true, '--if-exists' => true])
                ->runCommand('doctrine:database:create')
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

            $this
                ->runCommand('doctrine:database:drop', ['--force' => true, '--if-exists' => true])
                ->runCommand('doctrine:database:create')
                ->runCommand('doctrine:migrations:migrate', ['--no-interaction' => true])
            ;
        } elseif ($this->isSchemaPresent()) {
            if ($this->io->confirm('Seems like your database contains schema. Do you want to reset it?', false)) {
                $this->io->text('Dropping schema and creating schema...');

                $this
                    ->runCommand('doctrine:schema:drop', ['--force' => true])
                    ->runCommand('doctrine:migrations:migrate', ['--no-interaction' => true])
                ;
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
        $this->entityManager->createQuery('DELETE FROM Wallabag\CoreBundle\Entity\InternalSetting')->execute();
        $this->entityManager->createQuery('DELETE FROM Wallabag\CoreBundle\Entity\IgnoreOriginInstanceRule')->execute();

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
        if (!$this->runOtherCommands) {
            return $this;
        }

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
        $databaseName = $connection->getDatabase();

        try {
            $schemaManager = $connection->createSchemaManager();
        } catch (\Exception $exception) {
            // mysql & sqlite
            if (false !== strpos($exception->getMessage(), sprintf("Unknown database '%s'", $databaseName))) {
                return false;
            }

            // pgsql
            if (false !== strpos($exception->getMessage(), sprintf('database "%s" does not exist', $databaseName))) {
                return false;
            }

            throw $exception;
        }

        // custom verification for sqlite, since `getListDatabasesSQL` doesn't work for sqlite
        if ($connection->getDatabasePlatform() instanceof SqlitePlatform) {
            $params = $connection->getParams();

            if (isset($params['path']) && file_exists($params['path'])) {
                return true;
            }

            return false;
        }

        try {
            return \in_array($databaseName, $schemaManager->listDatabases(), true);
        } catch (DriverException $e) {
            // it means we weren't able to get database list, assume the database doesn't exist

            return false;
        }
    }

    /**
     * Check if the schema is already created.
     * If we found at least one table, it means the schema exists.
     *
     * @return bool
     */
    private function isSchemaPresent()
    {
        $schemaManager = $this->entityManager->getConnection()->createSchemaManager();

        return \count($schemaManager->listTableNames()) > 0 ? true : false;
    }
}
