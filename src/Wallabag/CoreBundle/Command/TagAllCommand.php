<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\CoreBundle\Helper\RuleBasedTagger;
use Wallabag\UserBundle\Repository\UserRepository;

class TagAllCommand extends ContainerAwareCommand
{
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
        $tagger = $this->getContainer()->get(RuleBasedTagger::class);

        $io->text(sprintf('Tagging entries for user <info>%s</info>...', $user->getUserName()));

        $entries = $tagger->tagAllForUser($user);

        $io->text('Persist ' . \count($entries) . ' entries... ');

        $em = $this->getDoctrine()->getManager();
        foreach ($entries as $entry) {
            $em->persist($entry);
        }
        $em->flush();

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
        return $this->getContainer()->get(UserRepository::class)->findOneByUserName($username);
    }

    private function getDoctrine()
    {
        return $this->getContainer()->get('doctrine');
    }
}
