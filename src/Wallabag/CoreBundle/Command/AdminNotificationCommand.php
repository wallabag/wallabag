<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wallabag\CoreBundle\Entity\Notification;
use Wallabag\CoreBundle\Notifications\InfoAction;
use Wallabag\UserBundle\Entity\User;

class AdminNotificationCommand extends AbstractNotificationCommand
{
    protected function configure()
    {
        $this
            ->setName('wallabag:notification:send')
            ->setDescription('Emits a notification to all users')
            ->setHelp('This command helps you send notifications to all of the users instance, or just for one user.')
            ->addArgument(
                'title',
                InputArgument::REQUIRED,
                'Title of your notification. This is required if if the type of notification is an admin one.'
            )
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'Message of your notification. This is required if the type of notification is an admin one.'
            )
            ->addOption(
                'link',
                'l',
                InputOption::VALUE_REQUIRED,
                'A link to display with the notification'
            )
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $username = $input->getArgument('username');

        $message = $input->getArgument('message');
        $title = $input->getArgument('title');

        $link = $input->getOption('link');

        if ($username) {
            try {
                $user = $this->getUser($username);
                $this->sendNotification($user, $title, $message, $link);
            } catch (NoResultException $e) {
                $output->writeln(sprintf('<error>User "%s" not found.</error>', $username));

                return 1;
            }
        } else {
            $users = $this->getDoctrine()->getRepository('WallabagUserBundle:User')->findAll();

            $output->writeln(sprintf('Sending notifications to %d user accounts. This can take some time.', count($users)));

            foreach ($users as $user) {
                $output->writeln(sprintf('Processing user %s', $user->getUsername()));
                $this->sendNotification($user, $title, $message, $link);
            }
            $output->writeln('Finished sending notifications.');
        }

        return 0;
    }

    /**
     * @param User $user
     * @param $title
     * @param $message
     * @param null $link
     */
    private function sendNotification(User $user, $title, $message, $link = null)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $notification = new Notification($user);
        $notification->setTitle($title)
            ->setDescription($message)
            ->setType(Notification::TYPE_ADMIN);

        if ($link) {
            $action = new InfoAction($link);

            $notification->addAction($action);
        }

        $em->persist($notification);
        $em->flush();

        $this->output->writeln(sprintf('Sent notification for user %s', $user->getUserName()));
    }
}
