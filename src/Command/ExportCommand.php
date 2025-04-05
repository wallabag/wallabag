<?php

namespace Wallabag\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\Helper\EntriesExport;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\UserRepository;

class ExportCommand extends Command
{
    protected static $defaultName = 'wallabag:export';
    protected static $defaultDescription = 'Export all entries for an user';

    public function __construct(
        private readonly EntryRepository $entryRepository,
        private readonly UserRepository $userRepository,
        private readonly EntriesExport $entriesExport,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $user = $this->userRepository->findOneByUserName($input->getArgument('username'));
        } catch (NoResultException $e) {
            $io->error(\sprintf('User "%s" not found.', $input->getArgument('username')));

            return 1;
        }

        $entries = $this->entryRepository
            ->getBuilderForAllByUser($user->getId())
            ->getQuery()
            ->getResult();

        $io->text(\sprintf('Exporting <info>%d</info> entrie(s) for user <info>%s</info>...', \count($entries), $user->getUserName()));

        $filePath = $input->getArgument('filepath');

        if (!$filePath) {
            $filePath = $this->projectDir . '/' . \sprintf('%s-export.json', $user->getUsername());
        }

        try {
            $data = $this->entriesExport
                ->setEntries($entries)
                ->updateTitle('All')
                ->updateAuthor('All')
                ->exportJsonData();
            file_put_contents($filePath, $data);
        } catch (\InvalidArgumentException $e) {
            $io->error(\sprintf('Error: "%s"', $e->getMessage()));

            return 1;
        }

        $io->success('Done.');

        return 0;
    }
}
