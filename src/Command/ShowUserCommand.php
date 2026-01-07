<?php

namespace Wallabag\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\Entity\User;
use Wallabag\Repository\UserRepository;

class ShowUserCommand extends Command
{
    protected static $defaultName = 'wallabag:user:show';
    protected static $defaultDescription = 'Show user details';

    protected SymfonyStyle $io;

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setHelp('This command shows the details for an user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'User to show details for'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');

        try {
            $user = $this->getUser($username);
            $this->showUser($user);
        } catch (NoResultException) {
            $this->io->error(\sprintf('User "%s" not found.', $username));

            return 1;
        }

        return 0;
    }

    private function showUser(User $user)
    {
        $this->io->listing([
            \sprintf('Username: %s', $user->getUsername()),
            \sprintf('Email: %s', $user->getEmail()),
            \sprintf('Display name: %s', $user->getName()),
            \sprintf('Creation date: %s', $user->getCreatedAt()->format('Y-m-d H:i:s')),
            \sprintf('Last login: %s', null !== $user->getLastLogin() ? $user->getLastLogin()->format('Y-m-d H:i:s') : 'never'),
            \sprintf('2FA (email) activated: %s', $user->isEmailTwoFactor() ? 'yes' : 'no'),
            \sprintf('2FA (OTP) activated: %s', $user->isGoogleAuthenticatorEnabled() ? 'yes' : 'no'),
        ]);
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
