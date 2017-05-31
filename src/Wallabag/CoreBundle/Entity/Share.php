<?php

namespace Wallabag\CoreBundle\Entity;

use Wallabag\FederationBundle\Entity\Account;
use Wallabag\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Share.
 *
 * @ORM\Entity
 */
class Share
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Wallabag\FederationBundle\Entity\Account")
     */
    private $userOrigin;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Wallabag\FederationBundle\Entity\Account")
     */
    private $userDestination;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(targetEntity="Wallabag\CoreBundle\Entity\Entry")
     */
    private $entry;

    /**
     * @var boolean
     *
     * @ORM\Column(name="accepted", type="boolean")
     */
    private $accepted;

    /**
     * Share constructor.
     */
    public function __construct()
    {
        $this->accepted = false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Account
     */
    public function getUserOrigin()
    {
        return $this->userOrigin;
    }

    /**
     * @param User $userOrigin
     * @return Share
     */
    public function setUserOrigin(User $userOrigin)
    {
        $this->userOrigin = $userOrigin;
        return $this;
    }

    /**
     * @return Account
     */
    public function getUserDestination()
    {
        return $this->userDestination;
    }

    /**
     * @param User $userDestination
     * @return Share
     */
    public function setUserDestination(User $userDestination)
    {
        $this->userDestination = $userDestination;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAccepted()
    {
        return $this->accepted;
    }

    /**
     * @param bool $accepted
     * @return Share
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;
        return $this;
    }

    /**
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param Entry $entry
     * @return Share
     */
    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
        return $this;
    }
}
