<?php

namespace Wallabag\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\Helper\EntryDeletionExpirationConfig;
use Wallabag\Repository\EntryDeletionRepository;

class PurgeEntryDeletionsCommand extends Command
{
    protected static $defaultName = 'wallabag:purge-entry-deletions';
    protected static $defaultDescription = 'Purge old entry deletion records';

    public function __construct(
        private readonly EntryDeletionRepository $entryDeletionRepository,
        private readonly EntryDeletionExpirationConfig $expirationConfig,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
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

        $dryRun = (bool) $input->getOption('dry-run');

        $cutoff = $this->expirationConfig->getCutoffDate();
        $count = $this->entryDeletionRepository->countAllBefore($cutoff);

        if ($dryRun) {
            $io->text('Dry run mode <info>enabled</info> (no records will be deleted)');
            if (0 === $count) {
                $io->success('No entry deletion records found.');
            } else {
                $io->success(\sprintf('Would have deleted %d records.', $count));
            }

            return 0;
        }

        if (0 === $count) {
            $io->success('No entry deletion records found.');

            return 0;
        }

        $confirmMessage = \sprintf(
            'Are you sure you want to delete records older than %s? (count: %d)',
            $cutoff->format('Y-m-d'),
            $count,
        );
        if (!$io->confirm($confirmMessage)) {
            return 0;
        }

        $this->entryDeletionRepository->deleteAllBefore($cutoff);

        $io->success(\sprintf('Successfully deleted %d records.', $count));

        return 0;
    }
}
