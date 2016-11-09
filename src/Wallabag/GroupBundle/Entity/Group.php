<?php

namespace Wallabag\GroupBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use Wallabag\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="`group`")
 */
class Group extends BaseGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Wallabag\UserBundle\Entity\User", mappedBy="groups", cascade={"persist"})
     */
    protected $users;

    public function getUsers()
    {
        return $this->users ?: $this->users = new ArrayCollection();
    }

    public function addUser(User $user)
    {
        if (!$this->getUsers()->contains($user)) {
            $this->getUsers()->add($user);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeUser(User $user)
    {
        if ($this->getUsers()->contains($user)) {
            $this->getUsers()->removeElement($user);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
