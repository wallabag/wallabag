<?php

namespace Wallabag\Command\Import;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\Entity\Config;
use Wallabag\Entity\Entry;
use Wallabag\Event\EntrySavedEvent;
use Wallabag\Helper\ContentProxy;
use Wallabag\MailImport\Mailbox;
use Wallabag\MailImport\MailImportSettings;
use Wallabag\MailImport\MailMessage;
use Wallabag\MailImport\MessageParser;
use Wallabag\Repository\ConfigRepository;
use Wallabag\Repository\EntryRepository;

class MailCommand extends Command
{
    private const LIMIT = 50;

    protected static $defaultName = 'wallabag:import:mail';

    protected static $defaultDescription = 'Save the links of the emails sent to the private addresses';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConfigRepository $configRepository,
        private readonly EntryRepository $entryRepository,
        private readonly ContentProxy $contentProxy,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly HttpBrowser $browser,
        private readonly MailImportSettings $settings,
        private readonly Mailbox $mailbox,
        private readonly MessageParser $parser,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Reads the mailbox configured with WALLABAG_MAIL_IMPORT_DSN and saves the links of each email in the account of its recipient. Run it periodically, e.g. with cron.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->settings->isEnabled()) {
            $io->note('Saving articles by email is disabled: WALLABAG_MAIL_IMPORT_ADDRESS and WALLABAG_MAIL_IMPORT_DSN must be set.');

            return 0;
        }

        $lock = fopen($this->projectDir . '/var/mail-import.lock', 'c');

        if (false === $lock || !flock($lock, \LOCK_EX | \LOCK_NB)) {
            $io->warning('Another import is already running.');

            return 0;
        }

        try {
            return $this->import($io);
        } finally {
            flock($lock, \LOCK_UN);
            fclose($lock);
        }
    }

    private function import(SymfonyStyle $io): int
    {
        $failed = false;
        $imported = 0;
        $this->mailbox->open();

        try {
            foreach ($this->mailbox->getMessages(self::LIMIT) as $message) {
                try {
                    $config = $this->findConfig($message);

                    if (null !== $config) {
                        $imported += $this->importMessage($config, $message);
                    }
                    $this->mailbox->delete($message->uid);
                } catch (\Throwable $e) {
                    $io->error(\sprintf('Unable to import message %d: %s', $message->uid, $e->getMessage()));
                    $failed = true;

                    if (!$this->entityManager->isOpen()) {
                        return 1;
                    }

                    $this->entityManager->clear();
                }
            }
        } finally {
            $this->mailbox->close();
        }

        $io->success(\sprintf('%d article(s) saved.', $imported));

        return $failed ? 1 : 0;
    }

    private function findConfig(MailMessage $message): ?Config
    {
        $token = $this->parser->getToken($this->settings->getAddress(), $message->recipients);

        if (null === $token) {
            return null;
        }

        $config = $this->configRepository->findOneBy(['mailImportToken' => $token]);

        if (null === $config || !$config->getUser()->isEnabled()) {
            return null;
        }

        return $config;
    }

    private function importMessage(Config $config, MailMessage $message): int
    {
        $user = $config->getUser();
        $urls = \array_slice($this->parser->getUrls($message->text, $message->html), 0, $this->settings->getMaxLinks());

        $previousToken = $this->tokenStorage->getToken();
        $this->browser->restart();
        $this->tokenStorage->setToken(new UsernamePasswordToken($user, 'main', $user->getRoles()));

        try {
            $entries = [];

            foreach ($urls as $url) {
                if (false !== $this->entryRepository->findByUrlAndUserId($url, $user->getId())) {
                    continue;
                }

                $entry = new Entry($user);
                $this->contentProxy->updateEntry($entry, $url);
                $this->entityManager->persist($entry);
                $entries[] = $entry;
            }

            $this->entityManager->flush();

            foreach ($entries as $entry) {
                $this->eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);
            }

            return \count($entries);
        } finally {
            $this->browser->restart();
            $this->tokenStorage->setToken($previousToken);
        }
    }
}
