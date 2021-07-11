<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\CoreBundle\Helper\EntriesExport;
use Wallabag\CoreBundle\Repository\EntryRepository;
use Wallabag\UserBundle\Repository\UserRepository;

class ExportCommand extends Command
{
    private $entryRepository;
    private $userRepository;
    private $entriesExport;
    private $projectDir;

    public function __construct(EntryRepository $entryRepository, UserRepository $userRepository, EntriesExport $entriesExport, string $projectDir)
    {
        $this->entryRepository = $entryRepository;
        $this->userRepository = $userRepository;
        $this->entriesExport = $entriesExport;
        $this->projectDir = $projectDir;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('wallabag:export')
            ->setDescription('Export all entries for an user')
            ->setHelp('This command helps you to export all entries for an user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'User from which to export entries'
            )
            ->addArgument(
                'filepath',
                InputArgument::OPTIONAL,
                'Path of the exported file'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $user = $this->userRepository->findOneByUserName($input->getArgument('username'));
        } catch (NoResultException $e) {
            $io->error(sprintf('User "%s" not found.', $input->getArgument('username')));

            return 1;
        }

        $entries = $this->entryRepository
            ->getBuilderForAllByUser($user->getId())
            ->getQuery()
            ->getResult();

        $io->text(sprintf('Exporting <info>%d</info> entrie(s) for user <info>%s</info>...', \count($entries), $user->getUserName()));

        $filePath = $input->getArgument('filepath');

        if (!$filePath) {
            $filePath = $this->projectDir . '/' . sprintf('%s-export.json', $user->getUsername());
        }

        try {
            $data = $this->entriesExport
                ->setEntries($entries)
                ->updateTitle('All')
                ->updateAuthor('All')
                ->exportJsonData();
            file_put_contents($filePath, $data);
        } catch (\InvalidArgumentException $e) {
            $io->error(sprintf('Error: "%s"', $e->getMessage()));

            return 1;
        }

        $io->success('Done.');

        return 0;
    }
}
