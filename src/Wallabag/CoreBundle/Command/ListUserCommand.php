<?php

namespace Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->getContainer()->get('wallabag_user.user_repository')->findAll();

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

        $io->success(sprintf('%s user(s) displayed.', count($users)));

        return 0;
    }
}
