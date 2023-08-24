<?php

namespace Wallabag\ImportBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\ImportBundle\Import\ChromeImport;
use Wallabag\ImportBundle\Import\DeliciousImport;
use Wallabag\ImportBundle\Import\ElcuratorImport;
use Wallabag\ImportBundle\Import\FirefoxImport;
use Wallabag\ImportBundle\Import\InstapaperImport;
use Wallabag\ImportBundle\Import\PinboardImport;
use Wallabag\ImportBundle\Import\PocketHtmlImport;
use Wallabag\ImportBundle\Import\ReadabilityImport;
use Wallabag\ImportBundle\Import\ShaarliImport;
use Wallabag\ImportBundle\Import\WallabagV1Import;
use Wallabag\ImportBundle\Import\WallabagV2Import;
use Wallabag\UserBundle\Entity\User;
use Wallabag\UserBundle\Repository\UserRepository;

class ImportCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;
    private UserRepository $userRepository;
    private WallabagV2Import $wallabagV2Import;
    private FirefoxImport $firefoxImport;
    private ChromeImport $chromeImport;
    private ReadabilityImport $readabilityImport;
    private InstapaperImport $instapaperImport;
    private PinboardImport $pinboardImport;
    private DeliciousImport $deliciousImport;
    private WallabagV1Import $wallabagV1Import;
    private ElcuratorImport $elcuratorImport;
    private ShaarliImport $shaarliImport;
    private PocketHtmlImport $pocketHtmlImport;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository,
        WallabagV2Import $wallabagV2Import,
        FirefoxImport $firefoxImport,
        ChromeImport $chromeImport,
        ReadabilityImport $readabilityImport,
        InstapaperImport $instapaperImport,
        PinboardImport $pinboardImport,
        DeliciousImport $deliciousImport,
        WallabagV1Import $wallabagV1Import,
        ElcuratorImport $elcuratorImport,
        ShaarliImport $shaarliImport,
        PocketHtmlImport $pocketHtmlImport
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
        $this->wallabagV2Import = $wallabagV2Import;
        $this->firefoxImport = $firefoxImport;
        $this->chromeImport = $chromeImport;
        $this->readabilityImport = $readabilityImport;
        $this->instapaperImport = $instapaperImport;
        $this->pinboardImport = $pinboardImport;
        $this->deliciousImport = $deliciousImport;
        $this->wallabagV1Import = $wallabagV1Import;
        $this->elcuratorImport = $elcuratorImport;
        $this->shaarliImport = $shaarliImport;
        $this->pocketHtmlImport = $pocketHtmlImport;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('wallabag:import')
            ->setDescription('Import entries from a JSON export')
            ->addArgument('username', InputArgument::REQUIRED, 'User to populate')
            ->addArgument('filepath', InputArgument::REQUIRED, 'Path to the JSON file')
            ->addOption('importer', null, InputOption::VALUE_OPTIONAL, 'The importer to use: v1, v2, instapaper, pinboard, delicious, readability, firefox, chrome, elcurator, shaarli or pocket', 'v1')
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

        // Turning off doctrine default logs queries for saving memory
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        if ($input->getOption('useUserId')) {
            $entityUser = $this->userRepository->findOneById($input->getArgument('username'));
        } else {
            $entityUser = $this->userRepository->findOneByUsername($input->getArgument('username'));
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

        $this->tokenStorage->setToken($token);
        $user = $this->tokenStorage->getToken()->getUser();

        switch ($input->getOption('importer')) {
            case 'v2':
                $import = $this->wallabagV2Import;
                break;
            case 'firefox':
                $import = $this->firefoxImport;
                break;
            case 'chrome':
                $import = $this->chromeImport;
                break;
            case 'readability':
                $import = $this->readabilityImport;
                break;
            case 'instapaper':
                $import = $this->instapaperImport;
                break;
            case 'pinboard':
                $import = $this->pinboardImport;
                break;
            case 'delicious':
                $import = $this->deliciousImport;
                break;
            case 'elcurator':
                $import = $this->elcuratorImport;
                break;
            case 'shaarli':
                $import = $this->shaarliImport;
                break;
            case 'pocket':
                $import = $this->pocketHtmlImport;
                break;
            default:
                $import = $this->wallabagV1Import;
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

        $this->entityManager->clear();

        $output->writeln('End : ' . (new \DateTime())->format('d-m-Y G:i:s') . ' ---');

        return 0;
    }
}
