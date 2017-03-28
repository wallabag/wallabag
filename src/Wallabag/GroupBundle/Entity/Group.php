<?php

namespace Wallabag\GroupBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use Wallabag\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Wallabag\GroupBundle\Repository\GroupRepository")
 * @ORM\Table(name="`group`")
 */
class Group extends BaseGroup
{
    /**
     * User Roles.
     */

    /** User can only preview presentations */
    const ROLE_READ_ONLY = 1;

    /** User can create new presentations */
    const ROLE_WRITE = 2;

    /** User can manage all group presentations */
    const ROLE_MANAGE_ENTRIES = 3;

    /** User can manage users in the group */
    const ROLE_MANAGE_USERS = 5;

    /** User can rename and delete the group */
    const ROLE_ADMIN = 10;

    /**
     * Group join access.
     */

    /** Any user can join the group */
    const ACCESS_OPEN = 1;

    /** An user needs to request to join the group */
    const ACCESS_REQUEST = 2;

    /** An user need the password to access the group */
    const ACCESS_PASSWORD = 3;

    /** An user needs to be invited to join the group */
    const ACCESS_INVITATION_ONLY = 4;

    /** An user needs to be invited to join the group, and the group is not publicly listed */
    const ACCESS_HIDDEN = 10;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", options={"default" : 1})
     */
    protected $acceptSystem;

    /**
     * @ORM\Column(type="integer", options={"default" : 2})
     */
    protected $defaultRole;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $password;
    protected $plainPassword;

    /**
     * @ORM\ManyToMany(targetEntity="Wallabag\CoreBundle\Entity\Entry", mappedBy="groupShares", cascade={"persist"})
     */
    protected $entries;

    /**
     * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="group", cascade={"persist"})
     */
    protected $users;

    public function __construct($name = '', array $roles = [])
    {
        parent::__construct($name, $roles);
        $this->defaultRole = self::ROLE_READ_ONLY;
        $this->acceptSystem = self::ACCESS_REQUEST;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        $userObj = new ArrayCollection();
        foreach ($this->users as $userGroup) {
            /* @var UserGroup $userGroup */
            $userObj->add($userGroup->getUser());
        }

        return $userObj;
    }

    /**
     * @return int
     */
    public function getDefaultRole()
    {
        return $this->defaultRole;
    }

    /**
     * @return int
     */
    public function getAcceptSystem()
    {
        return $this->acceptSystem;
    }

    /**
     * @param int $acceptSystem
     */
    public function setAcceptSystem($acceptSystem)
    {
        $this->acceptSystem = $acceptSystem;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password ?: '';
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword ?: '';
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @param int $defaultRole
     */
    public function setDefaultRole($defaultRole)
    {
        $this->defaultRole = $defaultRole;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getRequests()
    {
        $requests = new ArrayCollection();
        foreach ($this->users as $user) /* @var UserGroup $user */
        {
            if (!$user->isAccepted()) {
                $requests->add($user->getUser());
            }
        }

        return $requests;
    }

    public function getInvited()
    {
        $invited = new ArrayCollection();
        foreach ($this->users as $userGroup) /* @var UserGroup $userGroup */
        {
            if ($userGroup->getInvitation()) {
                $invited->add($userGroup);
            }
        }

        return $invited;
    }
}
