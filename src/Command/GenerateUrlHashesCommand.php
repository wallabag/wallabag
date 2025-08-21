<?php

namespace Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wallabag\Entity\User;
use Wallabag\Helper\UrlHasher;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\UserRepository;

class GenerateUrlHashesCommand extends Command
{
    protected static $defaultName = 'wallabag:generate-hashed-urls';
    protected static $defaultDescription = 'Generates hashed urls for each entry';

    protected OutputInterface $output;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntryRepository $entryRepository,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setHelp('This command helps you to generates hashes of the url of each entry, to check through API if an URL is already saved')
            ->addArgument('username', InputArgument::OPTIONAL, 'User to process entries');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $username = (string) $input->getArgument('username');

        if ($username) {
            try {
                $user = $this->getUser($username);
                $this->generateHashedUrls($user);
            } catch (NoResultException) {
                $output->writeln(\sprintf('<error>User "%s" not found.</error>', $username));

                return 1;
            }
        } else {
            $users = $this->userRepository->findAll();

            $output->writeln(\sprintf('Generating hashed urls for "%d" users', \count($users)));

            foreach ($users as $user) {
                $output->writeln(\sprintf('Processing user: %s', $user->getUsername()));
                $this->generateHashedUrls($user);
            }
            $output->writeln('Finished generated hashed urls');
        }

        return 0;
    }

    private function generateHashedUrls(User $user)
    {
        $entries = $this->entryRepository->findByEmptyHashedUrlAndUserId($user->getId());

        $i = 1;
        foreach ($entries as $entry) {
            $entry->setHashedUrl(UrlHasher::hashUrl($entry->getUrl()));
            $this->entityManager->persist($entry);

            if (0 === ($i % 20)) {
                $this->entityManager->flush();
            }
            ++$i;
        }

        $this->entityManager->flush();

        $this->output->writeln(\sprintf('Generated hashed urls for user: %s', $user->getUserName()));
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
