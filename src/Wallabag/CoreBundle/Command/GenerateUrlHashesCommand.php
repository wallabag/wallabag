<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wallabag\UserBundle\Entity\User;

class GenerateUrlHashesCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('wallabag:generate-hashed-urls')
            ->setDescription('Generates hashed urls for each entry')
            ->setHelp('This command helps you to generates hashes of the url of each entry, to check through API if an URL is already saved')
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                'User to process entries'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $username = $input->getArgument('username');

        if ($username) {
            try {
                $user = $this->getUser($username);
                $this->generateHashedUrls($user);
            } catch (NoResultException $e) {
                $output->writeln(sprintf('<error>User "%s" not found.</error>', $username));

                return 1;
            }
        } else {
            $users = $this->getDoctrine()->getRepository('WallabagUserBundle:User')->findAll();

            $output->writeln(sprintf('Generating hashed urls for the %d user account entries', count($users)));

            foreach ($users as $user) {
                $output->writeln(sprintf('Processing user %s', $user->getUsername()));
                $this->generateHashedUrls($user);
            }
            $output->writeln(sprintf('Finished generated hashed urls'));
        }

        return 0;
    }

    /**
     * @param User $user
     */
    private function generateHashedUrls(User $user)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $this->getDoctrine()->getRepository('WallabagCoreBundle:Entry');

        $entries = $repo->findByUser($user->getId());

        foreach ($entries as $entry) {
            $entry->setHashedUrl(hash('sha512', $entry->getUrl()));
            $em->persist($entry);
            $em->flush();
        }

        $this->output->writeln(sprintf('Generated hashed urls for user %s', $user->getUserName()));
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
