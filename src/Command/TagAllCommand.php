<?php

namespace Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\Entity\User;
use Wallabag\Helper\RuleBasedTagger;
use Wallabag\Repository\UserRepository;

class TagAllCommand extends Command
{
    protected static $defaultName = 'wallabag:tag:all';
    protected static $defaultDescription = 'Tag all entries using the tagging rules.';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RuleBasedTagger $ruleBasedTagger,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'User to tag entries for.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $user = $this->getUser($input->getArgument('username'));
        } catch (NoResultException) {
            $io->error(\sprintf('User "%s" not found.', $input->getArgument('username')));

            return 1;
        }

        $io->text(\sprintf('Tagging entries for user <info>%s</info>...', $user->getUserName()));

        $entries = $this->ruleBasedTagger->tagAllForUser($user);

        $io->text('Persist ' . \count($entries) . ' entries... ');

        foreach ($entries as $entry) {
            $this->entityManager->persist($entry);
        }
        $this->entityManager->flush();

        $io->success('Done.');

        return 0;
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
