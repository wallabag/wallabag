<?php

namespace Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wallabag:user:list')
            ->setDescription('List all users')
            ->setHelp('This command list all existing users')
            ->addArgument('search', InputArgument::OPTIONAL, 'Filter list by given search term')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Max number of displayed users', 100)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->getContainer()->get('wallabag_user.user_repository')
            ->getQueryBuilderForSearch($input->getArgument('search'))
            ->setMaxResults($input->getOption('limit'))
            ->getQuery()
            ->getResult();

        $nbUsers = $this->getContainer()->get('wallabag_user.user_repository')
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
            sprintf(
                '%s/%s%s user(s) displayed.',
                \count($users),
                $nbUsers,
                null === $input->getArgument('search') ? '' : ' (filtered)'
            )
        );

        return 0;
    }
}
