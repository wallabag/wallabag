<?php

namespace Wallabag\CoreBundle\Command;

use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Wallabag\CoreBundle\Entity\Config;
use Craue\ConfigBundle\Entity\Setting;

class InstallCommand extends ContainerAwareCommand
{
    /**
     * @var InputInterface
     */
    protected $defaultInput;

    /**
     * @var OutputInterface
     */
    protected $defaultOutput;

    /**
     * @var array
     */
    protected $functionExists = [
        'curl_exec',
        'curl_multi_init',
    ];

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
        $this->defaultOutput = $output;

        $output->writeln('<info>Installing wallabag...</info>');
        $output->writeln('');

        $this
            ->checkRequirements()
            ->setupDatabase()
            ->setupAdmin()
            ->setupConfig()
            ->runMigrations()
        ;

        $output->writeln('<info>wallabag has been successfully installed.</info>');
        $output->writeln('<comment>Just execute `php bin/console server:run --env=prod` for using wallabag: http://localhost:8000</comment>');
    }

    protected function checkRequirements()
    {
        $this->defaultOutput->writeln('<info><comment>Step 1 of 5.</comment> Checking system requirements.</info>');
        $doctrineManager = $this->getContainer()->get('doctrine')->getManager();

        $rows = [];

        // testing if database driver exists
        $fulfilled = true;
        $label = '<comment>PDO Driver (%s)</comment>';
        $status = '<info>OK!</info>';
        $help = '';

        if (!extension_loaded($this->getContainer()->getParameter('database_driver'))) {
            $fulfilled = false;
            $status = '<error>ERROR!</error>';
            $help = 'Database driver "'.$this->getContainer()->getParameter('database_driver').'" is not installed.';
        }

        $rows[] = [sprintf($label, $this->getContainer()->getParameter('database_driver')), $status, $help];

        // testing if connection to the database can be etablished
        $label = '<comment>Database connection</comment>';
        $status = '<info>OK!</info>';
        $help = '';

        try {
            $conn = $this->getContainer()->get('doctrine')->getManager()->getConnection();
            $conn->connect();
        } catch (\Exception $e) {
            if (false === strpos($e->getMessage(), 'Unknown database')
                && false === strpos($e->getMessage(), 'database "'.$this->getContainer()->getParameter('database_name').'" does not exist')) {
                $fulfilled = false;
                $status = '<error>ERROR!</error>';
                $help = 'Can\'t connect to the database: '.$e->getMessage();
            }
        }

        $rows[] = [$label, $status, $help];

        // check MySQL & PostgreSQL version
        $label = '<comment>Database version</comment>';
        $status = '<info>OK!</info>';
        $help = '';

        // now check if MySQL isn't too old to handle utf8mb4
        if ($conn->isConnected() && 'mysql' === $conn->getDatabasePlatform()->getName()) {
            $version = $conn->query('select version()')->fetchColumn();
            $minimalVersion = '5.5.4';

            if (false === version_compare($version, $minimalVersion, '>')) {
                $fulfilled = false;
                $status = '<error>ERROR!</error>';
                $help = 'Your MySQL version ('.$version.') is too old, consider upgrading ('.$minimalVersion.'+).';
            }
        }

        // testing if PostgreSQL > 9.1
        if ($conn->isConnected() && 'postgresql' === $conn->getDatabasePlatform()->getName()) {
            // return version should be like "PostgreSQL 9.5.4 on x86_64-apple-darwin15.6.0, compiled by Apple LLVM version 8.0.0 (clang-800.0.38), 64-bit"
            $version = $doctrineManager->getConnection()->query('SELECT version();')->fetchColumn();

            preg_match('/PostgreSQL ([0-9\.]+)/i', $version, $matches);

            if (isset($matches[1]) & version_compare($matches[1], '9.2.0', '<')) {
                $fulfilled = false;
                $status = '<error>ERROR!</error>';
                $help = 'PostgreSQL should be greater than 9.1 (actual version: '.$matches[1].')';
            }
        }

        $rows[] = [$label, $status, $help];

        foreach ($this->functionExists as $functionRequired) {
            $label = '<comment>'.$functionRequired.'</comment>';
            $status = '<info>OK!</info>';
            $help = '';

            if (!function_exists($functionRequired)) {
                $fulfilled = false;
                $status = '<error>ERROR!</error>';
                $help = 'You need the '.$functionRequired.' function activated';
            }

            $rows[] = [$label, $status, $help];
        }

        $table = new Table($this->defaultOutput);
        $table
            ->setHeaders(['Checked', 'Status', 'Recommendation'])
            ->setRows($rows)
            ->render();

        if (!$fulfilled) {
            throw new \RuntimeException('Some system requirements are not fulfilled. Please check output messages and fix them.');
        }

        $this->defaultOutput->writeln('<info>Success! Your system can run wallabag properly.</info>');

        $this->defaultOutput->writeln('');

        return $this;
    }

    protected function setupDatabase()
    {
        $this->defaultOutput->writeln('<info><comment>Step 2 of 5.</comment> Setting up database.</info>');

        // user want to reset everything? Don't care about what is already here
        if (true === $this->defaultInput->getOption('reset')) {
            $this->defaultOutput->writeln('Dropping database, creating database and schema, clearing the cache');

            $this
                ->runCommand('doctrine:database:drop', ['--force' => true])
                ->runCommand('doctrine:database:create')
                ->runCommand('doctrine:schema:create')
                ->runCommand('cache:clear')
            ;

            $this->defaultOutput->writeln('');

            return $this;
        }

        if (!$this->isDatabasePresent()) {
            $this->defaultOutput->writeln('Creating database and schema, clearing the cache');

            $this
                ->runCommand('doctrine:database:create')
                ->runCommand('doctrine:schema:create')
                ->runCommand('cache:clear')
            ;

            $this->defaultOutput->writeln('');

            return $this;
        }

        $questionHelper = $this->getHelper('question');
        $question = new ConfirmationQuestion('It appears that your database already exists. Would you like to reset it? (y/N)', false);

        if ($questionHelper->ask($this->defaultInput, $this->defaultOutput, $question)) {
            $this->defaultOutput->writeln('Dropping database, creating database and schema');

            $this
                ->runCommand('doctrine:database:drop', ['--force' => true])
                ->runCommand('doctrine:database:create')
                ->runCommand('doctrine:schema:create')
            ;
        } elseif ($this->isSchemaPresent()) {
            $question = new ConfirmationQuestion('Seems like your database contains schema. Do you want to reset it? (y/N)', false);
            if ($questionHelper->ask($this->defaultInput, $this->defaultOutput, $question)) {
                $this->defaultOutput->writeln('Dropping schema and creating schema');

                $this
                    ->runCommand('doctrine:schema:drop', ['--force' => true])
                    ->runCommand('doctrine:schema:create')
                ;
            }
        } else {
            $this->defaultOutput->writeln('Creating schema');

            $this
                ->runCommand('doctrine:schema:create')
            ;
        }

        $this->defaultOutput->writeln('Clearing the cache');
        $this->runCommand('cache:clear');

        $this->defaultOutput->writeln('');

        return $this;
    }

    protected function setupAdmin()
    {
        $this->defaultOutput->writeln('<info><comment>Step 3 of 5.</comment> Administration setup.</info>');

        $questionHelper = $this->getHelperSet()->get('question');
        $question = new ConfirmationQuestion('Would you like to create a new admin user (recommended) ? (Y/n)', true);

        if (!$questionHelper->ask($this->defaultInput, $this->defaultOutput, $question)) {
            return $this;
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $userManager = $this->getContainer()->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $question = new Question('Username (default: wallabag) :', 'wallabag');
        $user->setUsername($questionHelper->ask($this->defaultInput, $this->defaultOutput, $question));

        $question = new Question('Password (default: wallabag) :', 'wallabag');
        $user->setPlainPassword($questionHelper->ask($this->defaultInput, $this->defaultOutput, $question));

        $question = new Question('Email:', '');
        $user->setEmail($questionHelper->ask($this->defaultInput, $this->defaultOutput, $question));

        $user->setEnabled(true);
        $user->addRole('ROLE_SUPER_ADMIN');

        $em->persist($user);

        // dispatch a created event so the associated config will be created
        $event = new UserEvent($user);
        $this->getContainer()->get('event_dispatcher')->dispatch(FOSUserEvents::USER_CREATED, $event);

        $this->defaultOutput->writeln('');

        return $this;
    }

    protected function setupConfig()
    {
        $this->defaultOutput->writeln('<info><comment>Step 4 of 5.</comment> Config setup.</info>');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // cleanup before insert new stuff
        $em->createQuery('DELETE FROM CraueConfigBundle:Setting')->execute();

        $settings = [
            [
                'name' => 'share_public',
                'value' => '1',
                'section' => 'entry',
            ],
            [
                'name' => 'carrot',
                'value' => '1',
                'section' => 'entry',
            ],
            [
                'name' => 'share_diaspora',
                'value' => '1',
                'section' => 'entry',
            ],
            [
                'name' => 'diaspora_url',
                'value' => 'http://diasporapod.com',
                'section' => 'entry',
            ],
            [
                'name' => 'share_unmark',
                'value' => '1',
                'section' => 'entry',
            ],
            [
                'name' => 'unmark_url',
                'value' => 'https://unmark.it',
                'section' => 'entry',
            ],
            [
                'name' => 'share_shaarli',
                'value' => '1',
                'section' => 'entry',
            ],
            [
                'name' => 'shaarli_url',
                'value' => 'http://myshaarli.com',
                'section' => 'entry',
            ],
            [
                'name' => 'share_scuttle',
                'value' => '1',
                'section' => 'entry',
            ],
            [
                'name' => 'scuttle_url',
                'value' => 'http://scuttle.org',
                'section' => 'entry',
            ],
            [
                'name' => 'share_mail',
                'value' => '1',
                'section' => 'entry',
            ],
            [
                'name' => 'share_twitter',
                'value' => '1',
                'section' => 'entry',
            ],
            [
                'name' => 'export_epub',
                'value' => '1',
                'section' => 'export',
            ],
            [
                'name' => 'export_mobi',
                'value' => '1',
                'section' => 'export',
            ],
            [
                'name' => 'export_pdf',
                'value' => '1',
                'section' => 'export',
            ],
            [
                'name' => 'export_csv',
                'value' => '1',
                'section' => 'export',
            ],
            [
                'name' => 'export_json',
                'value' => '1',
                'section' => 'export',
            ],
            [
                'name' => 'export_txt',
                'value' => '1',
                'section' => 'export',
            ],
            [
                'name' => 'export_xml',
                'value' => '1',
                'section' => 'export',
            ],
            [
                'name' => 'import_with_redis',
                'value' => '0',
                'section' => 'import',
            ],
            [
                'name' => 'import_with_rabbitmq',
                'value' => '0',
                'section' => 'import',
            ],
            [
                'name' => 'show_printlink',
                'value' => '1',
                'section' => 'entry',
            ],
            [
                'name' => 'wallabag_support_url',
                'value' => 'https://www.wallabag.org/pages/support.html',
                'section' => 'misc',
            ],
            [
                'name' => 'wallabag_url',
                'value' => '',
                'section' => 'misc',
            ],
            [
                'name' => 'piwik_enabled',
                'value' => '0',
                'section' => 'analytics',
            ],
            [
                'name' => 'piwik_host',
                'value' => 'v2.wallabag.org',
                'section' => 'analytics',
            ],
            [
                'name' => 'piwik_site_id',
                'value' => '1',
                'section' => 'analytics',
            ],
            [
                'name' => 'demo_mode_enabled',
                'value' => '0',
                'section' => 'misc',
            ],
            [
                'name' => 'demo_mode_username',
                'value' => 'wallabag',
                'section' => 'misc',
            ],
            [
                'name' => 'download_images_enabled',
                'value' => '0',
                'section' => 'misc',
            ],
            [
                'name' => 'restricted_access',
                'value' => '0',
                'section' => 'entry',
            ],
        ];

        foreach ($settings as $setting) {
            $newSetting = new Setting();
            $newSetting->setName($setting['name']);
            $newSetting->setValue($setting['value']);
            $newSetting->setSection($setting['section']);
            $em->persist($newSetting);
        }

        $em->flush();

        $this->defaultOutput->writeln('');

        return $this;
    }

    protected function runMigrations()
    {
        $this->defaultOutput->writeln('<info><comment>Step 5 of 5.</comment> Run migrations.</info>');

        $this
            ->runCommand('doctrine:migrations:migrate', ['--no-interaction' => true]);
    }

    /**
     * Run a command.
     *
     * @param string $command
     * @param array  $parameters Parameters to this command (usually 'force' => true)
     */
    protected function runCommand($command, $parameters = [])
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
        $this->getContainer()->get('doctrine')->getManager()->getConnection()->close();

        if (0 !== $exitCode) {
            $this->getApplication()->setAutoExit(true);

            throw new \RuntimeException(
                'The command "'.$command."\" generates some errors: \n\n"
                .$output->fetch());
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
        $connection = $this->getContainer()->get('doctrine')->getManager()->getConnection();
        $databaseName = $connection->getDatabase();

        try {
            $schemaManager = $connection->getSchemaManager();
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
        if ('sqlite' === $schemaManager->getDatabasePlatform()->getName()) {
            $params = $this->getContainer()->get('doctrine.dbal.default_connection')->getParams();

            if (isset($params['path']) && file_exists($params['path'])) {
                return true;
            }

            return false;
        }

        try {
            return in_array($databaseName, $schemaManager->listDatabases());
        } catch (\Doctrine\DBAL\Exception\DriverException $e) {
            // it means we weren't able to get database list, assume the database doesn't exist

            return false;
        }
    }

    /**
     * Check if the schema is already created.
     * If we found at least oen table, it means the schema exists.
     *
     * @return bool
     */
    private function isSchemaPresent()
    {
        $schemaManager = $this->getContainer()->get('doctrine')->getManager()->getConnection()->getSchemaManager();

        return count($schemaManager->listTableNames()) > 0 ? true : false;
    }
}
