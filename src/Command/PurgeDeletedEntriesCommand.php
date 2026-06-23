<?php

namespace Wallabag\Command;

use Craue\ConfigBundle\Util\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\Repository\EntryRepository;
use Wallabag\Tools\Utils;

class PurgeDeletedEntriesCommand extends Command
{
    protected static $defaultName = 'wallabag:purge-deleted-entries';
    protected static $defaultDescription = 'Permanently removes soft-deleted entries older than the configured retention period';

    public function __construct(
        private readonly EntryRepository $entryRepository,
        private readonly Config $craueConfig,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Report how many entries would be removed without actually deleting them'
            )
            ->addOption(
                'older-than',
                null,
                InputOption::VALUE_REQUIRED,
                'Override the instance setting: purge entries deleted more than this many days ago'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $olderThan = $input->getOption('older-than');
        if (null !== $olderThan) {
            $lifetime = (int) $olderThan;
        } else {
            $lifetime = Utils::coerceNullableInt($this->craueConfig->get('deleted_entries_expiration_days'));
        }

        if (null === $lifetime) {
            $io->note('No expiration configured (deleted_entries_expiration_days is not set). Nothing to purge.');

            return Command::SUCCESS;
        }

        $dryRun = $input->getOption('dry-run');
        $before = new \DateTimeImmutable("-{$lifetime} days");

        $count = $dryRun
            ? $this->entryRepository->countDeletedEntriesOlderThan($before)
            : $this->entryRepository->purgeDeletedEntriesOlderThan($before);

        $verb = $dryRun ? 'would be purged' : 'purged';
        $io->success(sprintf('%d deleted entr%s %s (older than %d days).', $count, 1 === $count ? 'y' : 'ies', $verb, $lifetime));

        return Command::SUCCESS;
    }
}
