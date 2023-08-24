<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wallabag\CoreBundle\Event\EntrySavedEvent;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Wallabag\CoreBundle\Repository\EntryRepository;
use Wallabag\UserBundle\Repository\UserRepository;

class ReloadEntryCommand extends Command
{
    private EntryRepository $entryRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private ContentProxy $contentProxy;
    private EventDispatcherInterface $dispatcher;

    public function __construct(EntryRepository $entryRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, ContentProxy $contentProxy, EventDispatcherInterface $dispatcher)
    {
        $this->entryRepository = $entryRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->contentProxy = $contentProxy;
        $this->dispatcher = $dispatcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('wallabag:entry:reload')
            ->setDescription('Reload entries')
            ->setHelp('This command reload entries')
            ->addArgument('username', InputArgument::OPTIONAL, 'Reload entries only for the given user')
            ->addOption(
                'only-not-parsed',
                null,
                InputOption::VALUE_NONE,
                'Only reload entries which have `is_not_parsed` set to `true`'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $onlyNotParsed = (bool) $input->getOption('only-not-parsed');
        $userId = null;
        if ($username = $input->getArgument('username')) {
            try {
                $userId = $this->userRepository
                    ->findOneByUserName($username)
                    ->getId();
            } catch (NoResultException $e) {
                $io->error(sprintf('User "%s" not found.', $username));

                return 1;
            }
        }

        $methodName = $onlyNotParsed ? 'findAllEntriesIdByUserIdAndNotParsed' : 'findAllEntriesIdByUserId';
        $entryIds = $this->entryRepository->$methodName($userId);

        $nbEntries = \count($entryIds);
        if (!$nbEntries) {
            $io->success('No entry to reload.');

            return 0;
        }

        $io->note(
            sprintf(
                "You're going to reload %s entries. Depending on the number of entry to reload, this could be a very long process.",
                $nbEntries
            )
        );

        if (!$io->confirm('Are you sure you want to proceed?')) {
            return 0;
        }

        $progressBar = $io->createProgressBar($nbEntries);

        $progressBar->start();
        foreach ($entryIds as $entryId) {
            $entry = $this->entryRepository->find($entryId);

            $this->contentProxy->updateEntry($entry, $entry->getUrl());
            $this->entityManager->persist($entry);
            $this->entityManager->flush();

            $this->dispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);
            $progressBar->advance();

            $this->entityManager->detach($entry);
        }
        $progressBar->finish();

        $io->newLine(2);
        $io->success('Done.');

        return 0;
    }
}
