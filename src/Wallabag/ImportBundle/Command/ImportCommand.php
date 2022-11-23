<?php

namespace Wallabag\ImportBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\ImportBundle\Import\ChromeImport;
use Wallabag\ImportBundle\Import\DeliciousImport;
use Wallabag\ImportBundle\Import\FirefoxImport;
use Wallabag\ImportBundle\Import\InstapaperImport;
use Wallabag\ImportBundle\Import\PinboardImport;
use Wallabag\ImportBundle\Import\ReadabilityImport;
use Wallabag\ImportBundle\Import\WallabagV1Import;
use Wallabag\ImportBundle\Import\WallabagV2Import;
use Wallabag\UserBundle\Entity\User;

class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wallabag:import')
            ->setDescription('Import entries from a JSON export')
            ->addArgument('username', InputArgument::REQUIRED, 'User to populate')
            ->addArgument('filepath', InputArgument::REQUIRED, 'Path to the JSON file')
            ->addOption('importer', null, InputOption::VALUE_OPTIONAL, 'The importer to use: v1, v2, instapaper, pinboard, delicious, readability, firefox or chrome', 'v1')
            ->addOption('markAsRead', null, InputOption::VALUE_OPTIONAL, 'Mark all entries as read', false)
            ->addOption('useUserId', null, InputOption::VALUE_NONE, 'Use user id instead of username to find account')
            ->addOption('disableContentUpdate', null, InputOption::VALUE_NONE, 'Disable fetching updated content from URL')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start : ' . (new \DateTime())->format('d-m-Y G:i:s') . ' ---');

        if (!file_exists($input->getArgument('filepath'))) {
            throw new Exception(sprintf('File "%s" not found', $input->getArgument('filepath')));
        }

        $em = $this->getContainer()->get(ManagerRegistry::class)->getManager();
        // Turning off doctrine default logs queries for saving memory
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        if ($input->getOption('useUserId')) {
            $entityUser = $em->getRepository(User::class)->findOneById($input->getArgument('username'));
        } else {
            $entityUser = $em->getRepository(User::class)->findOneByUsername($input->getArgument('username'));
        }

        if (!\is_object($entityUser)) {
            throw new Exception(sprintf('User "%s" not found', $input->getArgument('username')));
        }

        // Authenticate user for paywalled websites
        $token = new UsernamePasswordToken(
            $entityUser,
            null,
            'main',
            $entityUser->getRoles());

        $this->getContainer()->get(TokenStorageInterface::class)->setToken($token);
        $user = $this->getContainer()->get(TokenStorageInterface::class)->getToken()->getUser();

        switch ($input->getOption('importer')) {
            case 'v2':
                $import = $this->getContainer()->get(WallabagV2Import::class);
                break;
            case 'firefox':
                $import = $this->getContainer()->get(FirefoxImport::class);
                break;
            case 'chrome':
                $import = $this->getContainer()->get(ChromeImport::class);
                break;
            case 'readability':
                $import = $this->getContainer()->get(ReadabilityImport::class);
                break;
            case 'instapaper':
                $import = $this->getContainer()->get(InstapaperImport::class);
                break;
            case 'pinboard':
                $import = $this->getContainer()->get(PinboardImport::class);
                break;
            case 'delicious':
                $import = $this->getContainer()->get(DeliciousImport::class);
                break;
            default:
                $import = $this->getContainer()->get(WallabagV1Import::class);
        }

        $import->setMarkAsRead($input->getOption('markAsRead'));
        $import->setDisableContentUpdate($input->getOption('disableContentUpdate'));
        $import->setUser($user);

        $res = $import
            ->setFilepath($input->getArgument('filepath'))
            ->import();

        if (true === $res) {
            $summary = $import->getSummary();
            $output->writeln('<info>' . $summary['imported'] . ' imported</info>');
            $output->writeln('<comment>' . $summary['skipped'] . ' already saved</comment>');
        }

        $em->clear();

        $output->writeln('End : ' . (new \DateTime())->format('d-m-Y G:i:s') . ' ---');

        return 0;
    }
}
