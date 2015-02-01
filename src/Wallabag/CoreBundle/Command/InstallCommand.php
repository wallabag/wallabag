<?php

namespace Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wallabag\CoreBundle\Entity\Users;
use Wallabag\CoreBundle\Entity\UsersConfig;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wallabag:install')
            ->setDescription('Wallabag installer.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Installing Wallabag.</info>');
        $output->writeln('');

        $this
            ->checkStep($output)
            ->setupStep($input, $output)
        ;

        $output->writeln('<info>Wallabag has been successfully installed.</info>');
        $output->writeln('<comment>Just execute `php app/console server:run` for using wallabag: http://localhost:8000</comment>');
    }

    protected function checkStep(OutputInterface $output)
    {
        $output->writeln('<info>Checking system requirements.</info>');

        $fulfilled = true;

        // @TODO: find a better way to check requirements
        $output->writeln('<comment>Check PCRE</comment>');
        if (extension_loaded('pcre')) {
            $output->writeln(' <info>OK</info>');
        } else {
            $fulfilled = false;
            $output->writeln(' <error>ERROR</error>');
            $output->writeln('<comment>You should enabled PCRE extension</comment>');
        }

        $output->writeln('<comment>Check DOM</comment>');
        if (extension_loaded('DOM')) {
            $output->writeln(' <info>OK</info>');
        } else {
            $fulfilled = false;
            $output->writeln(' <error>ERROR</error>');
            $output->writeln('<comment>You should enabled DOM extension</comment>');
        }

        if (!$fulfilled) {
            throw new RuntimeException('Some system requirements are not fulfilled. Please check output messages and fix them.');
        }

        $output->writeln('');

        return $this;
    }

    protected function setupStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Setting up database.</info>');

        $this->setupDatabase($input, $output);

        // if ($this->getHelperSet()->get('dialog')->askConfirmation($output, '<question>Load fixtures (Y/N)?</question>', false)) {
        //     $this->setupFixtures($input, $output);
        // }

        $output->writeln('');
        $output->writeln('<info>Administration setup.</info>');

        $this->setupAdmin($output);

        $output->writeln('');

        return $this;
    }

    protected function setupDatabase(InputInterface $input, OutputInterface $output)
    {
        if ($this->getHelperSet()->get('dialog')->askConfirmation($output, '<question>Drop current database (Y/N)?</question>', true)) {
            $connection = $this->getContainer()->get('doctrine')->getConnection();
            $params = $connection->getParams();

            $name = isset($params['path']) ? $params['path'] : (isset($params['dbname']) ? $params['dbname'] : false);
            unset($params['dbname']);

            if (!isset($params['path'])) {
                $name = $connection->getDatabasePlatform()->quoteSingleIdentifier($name);
            }

            $connection->getSchemaManager()->dropDatabase($name);
        } else {
            throw new \Exception("Install setup stopped, database need to be dropped. Please backup your current one and re-launch the install command.");
        }

        $this
            ->runCommand('doctrine:database:create', $input, $output)
            ->runCommand('doctrine:schema:create', $input, $output)
            ->runCommand('cache:clear', $input, $output)
            ->runCommand('assets:install', $input, $output)
            ->runCommand('assetic:dump', $input, $output)
        ;
    }

    protected function setupFixtures(InputInterface $input, OutputInterface $output)
    {
        $doctrineConfig = $this->getContainer()->get('doctrine.orm.entity_manager')->getConnection()->getConfiguration();
        $logger = $doctrineConfig->getSQLLogger();
        // speed up fixture load
        $doctrineConfig->setSQLLogger(null);
        $this->runCommand('doctrine:fixtures:load', $input, $output);
        $doctrineConfig->setSQLLogger($logger);
    }

    protected function setupAdmin(OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $user = new Users();
        $user->setUsername($dialog->ask($output, '<question>Username</question> <comment>(default: wallabag)</comment> :', 'wallabag'));
        $user->setPassword($dialog->ask($output, '<question>Password</question> <comment>(default: wallabag)</comment> :', 'wallabag'));
        $user->setEmail($dialog->ask($output, '<question>Email:</question>', ''));

        $em->persist($user);

        $pagerConfig = new UsersConfig();
        $pagerConfig->setUserId($user->getId());
        $pagerConfig->setName('pager');
        $pagerConfig->setValue(10);

        $em->persist($pagerConfig);

        // $languageConfig = new LanguageConfig();
        // $languageConfig->setUserId($user->getId());
        // $languageConfig->setName('language');
        // $languageConfig->setValue('en_EN.UTF8');

        // $em->persist($languageConfig);

        $em->flush();
    }

    protected function runCommand($command, InputInterface $input, OutputInterface $output)
    {
        $this
            ->getApplication()
            ->find($command)
            ->run($input, $output)
        ;

        return $this;
    }
}
