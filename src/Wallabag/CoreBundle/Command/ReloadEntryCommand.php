<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\CoreBundle\Event\EntrySavedEvent;

class ReloadEntryCommand extends ContainerAwareCommand
{
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
                $userId = $this->getContainer()
                    ->get('wallabag_user.user_repository')
                    ->findOneByUserName($username)
                    ->getId();
            } catch (NoResultException $e) {
                $io->error(sprintf('User "%s" not found.', $username));

                return 1;
            }
        }

        $entryRepository = $this->getContainer()->get('wallabag_core.entry_repository');
        $entryIds = $entryRepository->findAllEntriesIdByUserId($userId);

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

        $contentProxy = $this->getContainer()->get('wallabag_core.content_proxy');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $dispatcher = $this->getContainer()->get('event_dispatcher');

        $progressBar->start();
        foreach ($entryIds as $entryId) {
            $entry = $entryRepository->find($entryId);

            $contentProxy->updateEntry($entry, $entry->getUrl());
            $em->persist($entry);
            $em->flush();

            $dispatcher->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));
            $progressBar->advance();

            $em->detach($entry);
        }
        $progressBar->finish();

        $io->newLine(2);
        $io->success('Done.');

        return 0;
    }
}
