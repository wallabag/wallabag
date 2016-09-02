<?php

namespace Wallabag\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wallabag:import')
            ->setDescription('Import entries from a JSON export from a wallabag v1 instance')
            ->addArgument('userId', InputArgument::REQUIRED, 'User ID to populate')
            ->addArgument('filepath', InputArgument::REQUIRED, 'Path to the JSON file')
            ->addOption('importer', null, InputArgument::OPTIONAL, 'The importer to use: wallabag v1, v2 or browser', 'v1')
            ->addOption('markAsRead', null, InputArgument::OPTIONAL, 'Mark all entries as read', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start : '.(new \DateTime())->format('d-m-Y G:i:s').' ---');

        if (!file_exists($input->getArgument('filepath'))) {
            throw new Exception(sprintf('File "%s" not found', $input->getArgument('filepath')));
        }

        $em = $this->getContainer()->get('doctrine')->getManager();
        // Turning off doctrine default logs queries for saving memory
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $user = $em->getRepository('WallabagUserBundle:User')->findOneById($input->getArgument('userId'));

        if (!is_object($user)) {
            throw new Exception(sprintf('User with id "%s" not found', $input->getArgument('userId')));
        }

        switch ($input->getOption('importer')) {
            case 'v2':
                $wallabag = $this->getContainer()->get('wallabag_import.wallabag_v2.import');
                break;
            case 'browser':
                $wallabag = $this->getContainer()->get('wallabag_import.browser.import');
                break;
            case 'v1':
            default:
                $wallabag = $this->getContainer()->get('wallabag_import.wallabag_v1.import');
                break;
        }

        $wallabag->setMarkAsRead($input->getOption('markAsRead'));
        $wallabag->setUser($user);

        $res = $wallabag
            ->setFilepath($input->getArgument('filepath'))
            ->import();

        if (true === $res) {
            $summary = $wallabag->getSummary();
            $output->writeln('<info>'.$summary['imported'].' imported</info>');
            $output->writeln('<comment>'.$summary['skipped'].' already saved</comment>');
        }

        $em->clear();

        $output->writeln('End : '.(new \DateTime())->format('d-m-Y G:i:s').' ---');
    }
}
