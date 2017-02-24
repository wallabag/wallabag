<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\Entity\User;

class CleanDuplicatesCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    protected $output;

    protected $duplicates = 0;

    protected function configure()
    {
        $this
            ->setName('wallabag:clean-duplicates')
            ->setDescription('Cleans the database for duplicates')
            ->setHelp('This command helps you to clean your articles list in case of duplicates')
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                'User to clean'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $username = $input->getArgument('username');

        if ($username) {
            try {
                $user = $this->getUser($username);
                $this->cleanDuplicates($user);
            } catch (NoResultException $e) {
                $output->writeln(sprintf('<error>User "%s" not found.</error>', $username));

                return 1;
            }
        } else {
            $users = $this->getDoctrine()->getRepository('WallabagUserBundle:User')->findAll();

            $output->writeln(sprintf('Cleaning through %d user accounts', count($users)));

            foreach ($users as $user) {
                $output->writeln(sprintf('Processing user %s', $user->getUsername()));
                $this->cleanDuplicates($user);
            }
            $output->writeln(sprintf('Finished cleaning. %d duplicates found in total', $this->duplicates));
        }

        return 0;
    }

    /**
     * @param User $user
     */
    private function cleanDuplicates(User $user)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $this->getDoctrine()->getRepository('WallabagCoreBundle:Entry');

        $entries = $repo->getAllEntriesIdAndUrl($user->getId());

        $duplicatesCount = 0;
        $urls = [];
        foreach ($entries as $entry) {
            $url = $this->similarUrl($entry['url']);

            /* @var $entry Entry */
            if (in_array($url, $urls)) {
                ++$duplicatesCount;

                $em->remove($repo->find($entry['id']));
                $em->flush(); // Flushing at the end of the loop would require the instance not being online
            } else {
                $urls[] = $entry['url'];
            }
        }

        $this->duplicates += $duplicatesCount;

        $this->output->writeln(sprintf('Cleaned %d duplicates for user %s', $duplicatesCount, $user->getUserName()));
    }

    private function similarUrl($url)
    {
        if (in_array(substr($url, -1), ['/', '#'])) { // get rid of "/" and "#" and the end of urls
            return substr($url, 0, strlen($url));
        }

        return $url;
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
        return $this->getDoctrine()->getRepository('WallabagUserBundle:User')->findOneByUserName($username);
    }

    private function getDoctrine()
    {
        return $this->getContainer()->get('doctrine');
    }
}
