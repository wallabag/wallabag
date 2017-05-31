<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\User;

use Symfony\Component\EventDispatcher\Event;
use Wallabag\UserBundle\Entity\User;

/**
 * This event is fired when user-related stuff is made.
 */
abstract class UserEvent extends Event
{
    protected $user;

    /**
     * UserEvent constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return UserEvent
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }
}
