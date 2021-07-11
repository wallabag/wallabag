<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\CoreBundle\Helper\RuleBasedTagger;
use Wallabag\UserBundle\Repository\UserRepository;

class TagAllCommand extends Command
{
    private $entityManager;
    private $ruleBasedTagger;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, RuleBasedTagger $ruleBasedTagger, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->ruleBasedTagger = $ruleBasedTagger;
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('wallabag:tag:all')
            ->setDescription('Tag all entries using the tagging rules.')
            ->addArgument(
               'username',
               InputArgument::REQUIRED,
               'User to tag entries for.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $user = $this->getUser($input->getArgument('username'));
        } catch (NoResultException $e) {
            $io->error(sprintf('User "%s" not found.', $input->getArgument('username')));

            return 1;
        }
        $io->text(sprintf('Tagging entries for user <info>%s</info>...', $user->getUserName()));

        $entries = $this->ruleBasedTagger->tagAllForUser($user);

        $io->text('Persist entries... ');

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
     * @return \Wallabag\UserBundle\Entity\User
     */
    private function getUser($username)
    {
        return $this->userRepository->findOneByUserName($username);
    }
}
