<?php

namespace Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Wallabag\CoreBundle\Entity\User;
use Wallabag\CoreBundle\Entity\Config;

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

    protected function configure()
    {
        $this
            ->setName('wallabag:install')
            ->setDescription('Wallabag installer.')
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

        $output->writeln('<info>Installing Wallabag...</info>');
        $output->writeln('');

        $this
            ->checkRequirements()
            ->setupDatabase()
            ->setupAdmin()
            ->setupAsset()
        ;

        $output->writeln('<info>Wallabag has been successfully installed.</info>');
        $output->writeln('<comment>Just execute `php app/console server:run` for using wallabag: http://localhost:8000</comment>');
    }

    protected function checkRequirements()
    {
        $this->defaultOutput->writeln('<info><comment>Step 1 of 4.</comment> Checking system requirements.</info>');

        $fulfilled = true;

        // @TODO: find a better way to check requirements
        $label = '<comment>PCRE</comment>';
        if (extension_loaded('pcre')) {
            $status = '<info>OK!</info>';
            $help = '';
        } else {
            $fulfilled = false;
            $status = '<error>ERROR!</error>';
            $help = 'You should enabled PCRE extension';
        }
        $rows[] = array($label, $status, $help);

        $label = '<comment>DOM</comment>';
        if (extension_loaded('DOM')) {
            $status = '<info>OK!</info>';
            $help = '';
        } else {
            $fulfilled = false;
            $status = '<error>ERROR!</error>';
            $help = 'You should enabled DOM extension';
        }
        $rows[] = array($label, $status, $help);

        $this->getHelper('table')
            ->setHeaders(array('Checked', 'Status', 'Recommendation'))
            ->setRows($rows)
            ->render($this->defaultOutput);

        if (!$fulfilled) {
            throw new \RuntimeException('Some system requirements are not fulfilled. Please check output messages and fix them.');
        } else {
            $this->defaultOutput->writeln('<info>Success! Your system can run Wallabag properly.</info>');
        }

        $this->defaultOutput->writeln('');

        return $this;
    }

    protected function setupDatabase()
    {
        $this->defaultOutput->writeln('<info><comment>Step 2 of 4.</comment> Setting up database.</info>');

        // user want to reset everything? Don't care about what is already here
        if (true === $this->defaultInput->getOption('reset')) {
            $this->defaultOutput->writeln('Droping database, creating database and schema');

            $this
                ->runCommand('doctrine:database:drop', array('--force' => true))
                ->runCommand('doctrine:database:create')
                ->runCommand('doctrine:schema:create')
            ;

            return $this;
        }

        if (!$this->isDatabasePresent()) {
            $this->defaultOutput->writeln('Creating database and schema, clearing the cache');

            $this
                ->runCommand('doctrine:database:create')
                ->runCommand('doctrine:schema:create')
                ->runCommand('cache:clear')
            ;

            return $this;
        }

        $dialog = $this->getHelper('dialog');

        if ($dialog->askConfirmation($this->defaultOutput, '<question>It appears that your database already exists. Would you like to reset it? (y/N)</question> ', false)) {
            $this->defaultOutput->writeln('Droping database, creating database and schema');

            $this
                ->runCommand('doctrine:database:drop', array('--force' => true))
                ->runCommand('doctrine:database:create')
                ->runCommand('doctrine:schema:create')
            ;
        } elseif ($this->isSchemaPresent()) {
            if ($dialog->askConfirmation($this->defaultOutput, '<question>Seems like your database contains schema. Do you want to reset it? (y/N)</question> ', false)) {
                $this->defaultOutput->writeln('Droping schema and creating schema');

                $this
                    ->runCommand('doctrine:schema:drop', array('--force' => true))
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

        /*
        if ($this->getHelperSet()->get('dialog')->askConfirmation($this->defaultOutput, '<question>Load fixtures (Y/N)?</question>', false)) {
            $doctrineConfig = $this->getContainer()->get('doctrine.orm.entity_manager')->getConnection()->getConfiguration();
            $logger = $doctrineConfig->getSQLLogger();
            // speed up fixture load
            $doctrineConfig->setSQLLogger(null);
            $this->runCommand('doctrine:fixtures:load');
            $doctrineConfig->setSQLLogger($logger);
        }
        */

        $this->defaultOutput->writeln('');

        return $this;
    }

    protected function setupAdmin()
    {
        $this->defaultOutput->writeln('<info><comment>Step 3 of 4.</comment> Administration setup.</info>');

        $dialog = $this->getHelperSet()->get('dialog');

        if (false === $dialog->askConfirmation($this->defaultOutput, '<question>Would you like to create a new user ? (y/N)</question>', true)) {
            return $this;
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $user = new User();
        $user->setUsername($dialog->ask($this->defaultOutput, '<question>Username</question> <comment>(default: wallabag)</comment> :', 'wallabag'));
        $user->setPassword($dialog->ask($this->defaultOutput, '<question>Password</question> <comment>(default: wallabag)</comment> :', 'wallabag'));
        $user->setEmail($dialog->ask($this->defaultOutput, '<question>Email:</question>', ''));

        $em->persist($user);

        $config = new Config($user);
        $config->setTheme($this->getContainer()->getParameter('theme'));
        $config->setItemsPerPage($this->getContainer()->getParameter('items_on_page'));
        $config->setRssLimit($this->getContainer()->getParameter('rss_limit'));
        $config->setLanguage($this->getContainer()->getParameter('language'));

        $em->persist($config);

        $em->flush();

        $this->defaultOutput->writeln('');

        return $this;
    }

    protected function setupAsset()
    {
        $this->defaultOutput->writeln('<info><comment>Step 4 of 4.</comment> Installing assets.</info>');

        $this
            ->runCommand('assets:install')
            ->runCommand('assetic:dump')
        ;

        $this->defaultOutput->writeln('');

        return $this;
    }

    /**
     * Run a command.
     *
     * @param string $command
     * @param array  $parameters Parameters to this command (usually 'force' => true)
     */
    protected function runCommand($command, $parameters = array())
    {
        $parameters = array_merge(
            array('command' => $command),
            $parameters,
            array(
                '--no-debug' => true,
                '--env' => $this->defaultInput->getOption('env') ?: 'dev',
            )
        );

        if ($this->defaultInput->getOption('no-interaction')) {
            $parameters = array_merge($parameters, array('--no-interaction' => true));
        }

        $this->getApplication()->setAutoExit(false);
        $exitCode = $this->getApplication()->run(new ArrayInput($parameters), new NullOutput());

        if (0 !== $exitCode) {
            $this->getApplication()->setAutoExit(true);

            $errorMessage = sprintf('The command "%s" terminated with an error code: %u.', $command, $exitCode);
            $this->defaultOutput->writeln("<error>$errorMessage</error>");
            $exception = new \Exception($errorMessage, $exitCode);

            throw $exception;
        }

        // PDO does not always close the connection after Doctrine commands.
        // See https://github.com/symfony/symfony/issues/11750.
        $this->getContainer()->get('doctrine')->getManager()->getConnection()->close();

        return $this;
    }

    /**
     * Check if the database already exists.
     *
     * @return bool
     */
    private function isDatabasePresent()
    {
        $databaseName = $this->getContainer()->getParameter('database_name');

        try {
            $schemaManager = $this->getContainer()->get('doctrine')->getManager()->getConnection()->getSchemaManager();
        } catch (\Exception $exception) {
            if (false !== strpos($exception->getMessage(), sprintf("Unknown database '%s'", $databaseName))) {
                return false;
            }

            throw $exception;
        }

        // custom verification for sqlite, since `getListDatabasesSQL` doesn't work for sqlite
        if ('sqlite' == $schemaManager->getDatabasePlatform()->getName()) {
            $params = $this->getContainer()->get('doctrine.dbal.default_connection')->getParams();

            if (isset($params['path']) && file_exists($params['path'])) {
                return true;
            }

            return false;
        }

        return in_array($databaseName, $schemaManager->listDatabases());
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
