<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wallabag\UserBundle\Entity\User;

class ShowUserCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('wallabag:user:show')
            ->setDescription('Show user details')
            ->setHelp('This command shows the details for an user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'User to show details for'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $username = $input->getArgument('username');

        try {
            $user = $this->getUser($username);
            $this->showUser($user);
        } catch (NoResultException $e) {
            $output->writeln(sprintf('<error>User "%s" not found.</error>', $username));

            return 1;
        }

        return 0;
    }

    /**
     * @param User $user
     */
    private function showUser(User $user)
    {
        $this->output->writeln(sprintf('Username : %s', $user->getUsername()));
        $this->output->writeln(sprintf('Email : %s', $user->getEmail()));
        $this->output->writeln(sprintf('Display name : %s', $user->getName()));
        $this->output->writeln(sprintf('Creation date : %s', $user->getCreatedAt()->format('Y-m-d H:i:s')));
        $this->output->writeln(sprintf('Last login : %s', $user->getLastLogin() !== null ? $user->getLastLogin()->format('Y-m-d H:i:s') : 'never'));
        $this->output->writeln(sprintf('2FA activated: %s', $user->isTwoFactorAuthentication() ? 'yes' : 'no'));
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
        return $this->get('wallabag_user.user_repository')->findOneByUserName($username);
    }

    private function getDoctrine()
    {
        return $this->getContainer()->get('doctrine');
    }
}
