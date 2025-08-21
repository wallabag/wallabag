<?php

namespace Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wallabag\Entity\User;
use Wallabag\Event\EntryDeletedEvent;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\UserRepository;

class CleanDuplicatesCommand extends Command
{
    protected static $defaultName = 'wallabag:clean-duplicates';
    protected static $defaultDescription = 'Cleans the database for duplicates';

    protected SymfonyStyle $io;
    protected int $duplicates = 0;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntryRepository $entryRepository,
        private readonly UserRepository $userRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setHelp('This command helps you to clean your articles list in case of duplicates')
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                'User to clean'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');

        if ($username) {
            try {
                $user = $this->getUser($username);
                $this->cleanDuplicates($user);
            } catch (NoResultException) {
                $this->io->error(\sprintf('User "%s" not found.', $username));

                return 1;
            }

            $this->io->success('Finished cleaning.');
        } else {
            $users = $this->userRepository->findAll();

            $this->io->text(\sprintf('Cleaning through <info>%d</info> user accounts', \count($users)));

            foreach ($users as $user) {
                $this->io->text(\sprintf('Processing user <info>%s</info>', $user->getUsername()));
                $this->cleanDuplicates($user);
            }
            $this->io->success(\sprintf('Finished cleaning. %d duplicates found in total', $this->duplicates));
        }

        return 0;
    }

    private function cleanDuplicates(User $user)
    {
        $entries = $this->entryRepository->findAllEntriesIdAndUrlByUserId($user->getId());

        $duplicatesCount = 0;
        $urls = [];
        foreach ($entries as $entry) {
            $url = $this->similarUrl($entry['url']);

            /* @var $entry Entry */
            if (\in_array($url, $urls, true)) {
                ++$duplicatesCount;

                $entryToDelete = $this->entryRepository->find($entry['id']);

                // entry deleted, dispatch event about it!
                $this->eventDispatcher->dispatch(new EntryDeletedEvent($entryToDelete), EntryDeletedEvent::NAME);

                $this->entityManager->remove($entryToDelete);
                $this->entityManager->flush(); // Flushing at the end of the loop would require the instance not being online
            } else {
                $urls[] = $entry['url'];
            }
        }

        $this->duplicates += $duplicatesCount;

        $this->io->text(\sprintf('Cleaned <info>%d</info> duplicates for user <info>%s</info>', $duplicatesCount, $user->getUserName()));
    }

    private function similarUrl($url)
    {
        if (\in_array(substr((string) $url, -1), ['/', '#'], true)) { // get rid of "/" and "#" and the end of urls
            return substr((string) $url, 0, \strlen((string) $url));
        }

        return $url;
    }

    /**
     * Fetches a user from its username.
     *
     * @param string $username
     *
     * @return User
     */
    private function getUser($username)
    {
        return $this->userRepository->findOneByUserName($username);
    }
}
