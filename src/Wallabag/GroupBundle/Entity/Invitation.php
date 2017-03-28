<?php

namespace Wallabag\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="invitation")
 */
class Invitation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=6)
     */
    protected $code;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\OneToOne(targetEntity="UserGroup", mappedBy="invitation")
     */
    protected $userGroup;

    public function __construct(UserGroup $userGroup)
    {
        // generate identifier only once, here a 6 characters length code
        $this->code = substr(md5(uniqid(rand(), true)), 0, 6);
        $this->date = new \DateTime();
        $this->userGroup = $userGroup;
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * @param mixed $userGroup
     */
    public function setUserGroup(UserGroup $userGroup)
    {
        $this->userGroup = $userGroup;
    }
}
