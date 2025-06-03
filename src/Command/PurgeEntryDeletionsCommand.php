<?php

namespace Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\Repository\EntryDeletionRepository;

class PurgeEntryDeletionsCommand extends Command
{
    protected static $defaultName = 'wallabag:purge-entry-deletions';
    protected static $defaultDescription = 'Purge old entry deletion records';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntryDeletionRepository $entryDeletionRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption(
                'older-than',
                null,
                InputOption::VALUE_REQUIRED,
                'Purge records older than this date (format: YYYY-MM-DD)',
                '-30 days'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Do not actually delete records, just show what would be deleted'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $olderThan = $input->getOption('older-than');
        $dryRun = (bool) $input->getOption('dry-run');

        try {
            $date = new \DateTime($olderThan);
        } catch (\Exception $e) {
            $io->error(sprintf('Invalid date format: %s.\nYou can use any format supported by PHP (e.g. YYYY-MM-DD).', $olderThan));

            return 1;
        }

        $count = $this->entryDeletionRepository->countAllBefore($date);

        if ($dryRun) {
            $io->text('Dry run mode <info>enabled</info> (no records will be deleted)');

            return 0;
        }


        if (0 === $count) {
            $io->success('No entry deletion records found.');

            return 0;
        }

        if ($dryRun) {
            $io->success(sprintf('Would have deleted %d records.', $count));

            return 0;
        }

        $confirmMessage = sprintf(
            'Are you sure you want to delete records older than %s? (count: %d)',
            $date->format('Y-m-d'),
            $count,
        );
        if (!$io->confirm($confirmMessage)) {
            return 0;
        }

        $this->entryDeletionRepository->deleteAllBefore($date);

        $io->success(sprintf('Successfully deleted %d records.', $count));

        return 0;
    }
}
