<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wallabag\CoreBundle\Event\EntrySavedEvent;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Wallabag\CoreBundle\Repository\EntryRepository;
use Wallabag\UserBundle\Repository\UserRepository;

class ReloadEntryCommand extends Command
{
    private $entryRepository;
    private $userRepository;
    private $entityManager;
    private $contentProxy;
    private $dispatcher;

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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

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

        $entryIds = $this->entryRepository->findAllEntriesIdByUserId($userId);

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

            $this->dispatcher->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));
            $progressBar->advance();

            $this->entityManager->detach($entry);
        }
        $progressBar->finish();

        $io->newLine(2);
        $io->success('Done.');

        return 0;
    }
}
