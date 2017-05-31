<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Federation;

use Symfony\Component\EventDispatcher\Event;
use Wallabag\FederationBundle\Entity\Account;

abstract class FederationEvent extends Event
{
    protected $account;

    /**
     * FederationEvent constructor.
     * @param Account $account
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Account $account
     * @return FederationEvent
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
        return $this;
    }


}
