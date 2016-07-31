<?php

namespace Wallabag\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wallabag\CoreBundle\Entity\Config;

class MakeConfigCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wallabag:makeconfig')
            ->setDescription('Create config for all users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start : '.(new \DateTime())->format('d-m-Y G:i:s').' ---');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $usermanager = $this->getContainer()->get('fos_user.user_manager');

        $users = $usermanager->findUsers();

        foreach ($users as $user) {

            if (!is_object($user)) {
                throw new Exception(sprintf('User with id "%s" not found', $input->getArgument('userId')));
            }

            $config = new Config($user);
            $config->setTheme('material');
            $config->setItemsPerPage(12);
            $config->setRssLimit(50);
            $config->setReadingSpeed(1.0);
            $config->setLanguage('fr');

            $em->persist($config);
            $user->setConfig($config);
            $output->writeln('Config created for user ' . $user->getUsername());
            # $em->flush();
        }

        $output->writeln('All config created');

        $em->clear();
    }
}
