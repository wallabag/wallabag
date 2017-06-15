<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wallabag\CoreBundle\Entity\Notification;
use Wallabag\CoreBundle\Notifications\Action;
use Wallabag\UserBundle\Entity\User;

class ReleaseNotificationCommand extends AbstractNotificationCommand
{
    /** @var OutputInterface */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('wallabag:notification:release')
            ->setDescription('Emits a notification to all users to let them know of a new release')
            ->setHelp('This command helps you send a release notification to all of the users instance, or just for one user.')
            ->addArgument(
                'link',
                InputArgument::OPTIONAL,
                'A link to display with the notification'
            )
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $username = $input->getArgument('username');

        $link = $input->getArgument('link');

        if ($username) {
            try {
                $user = $this->getUser($username);
                $this->sendNotification($user, $link);
            } catch (NoResultException $e) {
                $output->writeln(sprintf('<error>User "%s" not found.</error>', $username));

                return 1;
            }
        } else {
            $users = $this->getDoctrine()->getRepository('WallabagUserBundle:User')->findAll();

            $output->writeln(sprintf('Sending notifications to %d user accounts. This can take some time.', count($users)));

            foreach ($users as $user) {
                $output->writeln(sprintf('Processing user %s', $user->getUsername()));
                $this->sendNotification($user, $link);
            }
            $output->writeln('Finished sending notifications.');
        }

        return 0;
    }

    /**
     * @param User $user
     */
    private function sendNotification(User $user, $link)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $notification = new Notification($user);
        $notification->setTitle('notifications.release.title')
            ->addParameter('%version%', $this->getContainer()->getParameter('wallabag_core.version'))
            ->setType(Notification::TYPE_RELEASE);
        if ($link) {
            $details = new Action();
            $details->setType(Action::TYPE_INFO)
                ->setLabel('notifications.release.details')
                ->setLink($link);
            $notification->addAction($details);
        }
        $em->persist($notification);
        $em->flush();

        $this->output->writeln(sprintf('Sent notification for user %s', $user->getUserName()));
    }
}
