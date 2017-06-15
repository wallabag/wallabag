<?php

namespace Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractNotificationCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    protected $output;

    protected function configure()
    {
        $this
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                'User to send the notification to'
            )
        ;
    }

    /**
     * Fetches a user from its username.
     *
     * @param string $username
     *
     * @return \Wallabag\UserBundle\Entity\User
     */
    protected function getUser($username)
    {
        return $this->getDoctrine()->getRepository('WallabagUserBundle:User')->findOneByUserName($username);
    }

    protected function getDoctrine()
    {
        return $this->getContainer()->get('doctrine');
    }
}
