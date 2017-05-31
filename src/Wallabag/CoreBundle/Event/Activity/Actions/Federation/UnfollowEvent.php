<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Federation;

use Wallabag\FederationBundle\Entity\Account;

/**
 * This event is fired as soon as an account is being unfollowed
 */
class UnfollowEvent extends FederationEvent
{
    const NAME = 'federation.unfollow';

    protected $follower;

    public function __construct(Account $accountFollowed, Account $follower)
    {
        parent::__construct($accountFollowed);
        $this->follower = $follower;
    }

    /**
     * @return Account
     */
    public function getFollower()
    {
        return $this->follower;
    }

    /**
     * @param Account $follower
     * @return UnfollowEvent
     */
    public function setFollower(Account $follower)
    {
        $this->follower = $follower;
        return $this;
    }
}
