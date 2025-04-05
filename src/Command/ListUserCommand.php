<?php

namespace Wallabag\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\Repository\UserRepository;

class ListUserCommand extends Command
{
    protected static $defaultName = 'wallabag:user:list';
    protected static $defaultDescription = 'List all users';

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setHelp('This command list all existing users')
            ->addArgument('search', InputArgument::OPTIONAL, 'Filter list by given search term')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Max number of displayed users', 100)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->userRepository
            ->getQueryBuilderForSearch($input->getArgument('search'))
            ->setMaxResults($input->getOption('limit'))
            ->getQuery()
            ->getResult();

        $nbUsers = $this->userRepository
            ->getSumUsers();

        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                $user->getUsername(),
                $user->getEmail(),
                $user->isEnabled() ? 'yes' : 'no',
                $user->hasRole('ROLE_SUPER_ADMIN') || $user->hasRole('ROLE_ADMIN') ? 'yes' : 'no',
            ];
        }

        $io->table(['username', 'email', 'is enabled?', 'is admin?'], $rows);

        $io->success(
            \sprintf(
                '%s/%s%s user(s) displayed.',
                \count($users),
                $nbUsers,
                null === $input->getArgument('search') ? '' : ' (filtered)'
            )
        );

        return 0;
    }
}
