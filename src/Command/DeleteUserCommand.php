<?php

namespace Wallabag\Command;

use Doctrine\ORM\NoResultException;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\Repository\UserRepository;

class DeleteUserCommand extends Command
{
    protected static $defaultName = 'wallabag:user:delete';

    protected static $defaultDescription = 'Delete a user and all their data';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserManagerInterface $userManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command deletes a user and all their data, such as their entries, annotations and settings. It asks for confirmation, use the --force option to skip it. Deleting an admin also requires the --force-admin option')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'User to delete',
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Delete the user without asking for confirmation',
            )
            ->addOption(
                'force-admin',
                null,
                InputOption::VALUE_NONE,
                'Allow deleting a user who is an admin',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');
        $force = (bool) $input->getOption('force');

        try {
            $user = $this->userRepository->findOneByUserName($username);
        } catch (NoResultException) {
            $io->error(\sprintf('User "%s" not found.', $username));

            return 1;
        }

        if (($user->hasRole('ROLE_SUPER_ADMIN') || $user->hasRole('ROLE_ADMIN')) && !$input->getOption('force-admin')) {
            $io->error(\sprintf('User "%s" is an admin and can only be deleted with the --force-admin option.', $username));

            return 1;
        }

        if (!$force && !$input->isInteractive()) {
            $io->error(\sprintf('User "%s" has not been deleted. Use the --force option to delete a user without confirmation.', $username));

            return 1;
        }

        if (!$force && !$io->confirm(\sprintf('Are you sure you want to delete the user "%s" and all their data? This cannot be undone.', $username), false)) {
            $io->note('The user has not been deleted.');

            return 0;
        }

        $this->userManager->deleteUser($user);

        $io->success(\sprintf('User "%s" deleted.', $username));

        return 0;
    }
}
