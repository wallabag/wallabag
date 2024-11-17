<?php

namespace Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\UserRepository;

class CleanDuplicatesCommand extends Command
{
    protected static $defaultName = 'wallabag:clean-duplicates';
    protected static $defaultDescription = 'Cleans the database for duplicates';

    protected SymfonyStyle $io;
    protected int $duplicates = 0;
    private EntityManagerInterface $entityManager;
    private EntryRepository $entryRepository;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager, EntryRepository $entryRepository, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->entryRepository = $entryRepository;
        $this->userRepository = $userRepository;

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
            } catch (NoResultException $e) {
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

                $this->entityManager->remove($this->entryRepository->find($entry['id']));
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
        if (\in_array(substr($url, -1), ['/', '#'], true)) { // get rid of "/" and "#" and the end of urls
            return substr($url, 0, \strlen($url));
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
