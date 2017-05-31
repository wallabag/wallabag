<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Wallabag\FederationBundle\Entity\Account;

/**
 * Change.
 *
 * This entity stores a datetime for each activity.
 *
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\ChangeRepository")
 * @ORM\Table(name="`activity`")
 */
class Activity
{
    /**
     * Object types
     */
    const ENTRY_OBJECT = 1;
    const TAG_OBJECT = 2;
    const USER_OBJECT = 3;
    const SHARE_OBJECT = 4;
    const GROUP_OBJECT = 5;
    const ANNOTATION_OBJECT = 6;
    const CONFIG_OBJECT = 7;
    const ACCOUNT_OBJECT = 8;

    /**
     * Events
     */

    /**
     * Entry events
     */
    const ENTRY_ADD = 10; // done
    const ENTRY_EDIT = 11; // done
    const ENTRY_READ = 12; // done
    const ENTRY_UNREAD = 13; // done
    const ENTRY_FAVOURITE = 14; // done
    const ENTRY_UNFAVOURITE = 15; // done
    const ENTRY_DELETE = 19; // done

    /**
     * Tag events
     */
    const TAG_CREATE = 20; // not yet implemented
    const TAG_EDIT = 21; // not yet implemented
    const TAG_REMOVE = 29; // not yet implemented

    /**
     * Entry - Tag events
     */
    const ENTRY_ADD_TAG = 30; // done
    const ENTRY_REMOVE_TAG = 39; // done

    /**
     * Entry - Annotation events
     */
    const ANNOTATION_ADD = 40; // done
    const ANNOTATION_EDIT = 41; // done
    const ANNOTATION_REMOVE = 49; // done

    /**
     * User events
     */
    const USER_CREATE = 50; // done
    const USER_EDIT = 51; // done
    const USER_REMOVE = 59; // done

    /**
     * Federation events
     */
    const FOLLOW_ACCOUNT = 61;
    const UNFOLLOW_ACCOUNT = 62;
    const RECOMMEND_ENTRY = 63;

    /**
     * Share events
     */
    const USER_SHARE_CREATED = 70; // done
    const USER_SHARE_ACCEPTED = 71; // done
    const USER_SHARE_REFUSED = 72; // done
    const USER_SHARE_CANCELLED = 79; // not implemented yet

    /**
     * Group events
     */
    const GROUP_CREATE = 80;
    const GROUP_EDIT = 81;
    const GROUP_ADD_MEMBER = 82;
    const GROUP_EDIT_MEMBER = 83;
    const GROUP_REMOVE_MEMBER = 84;
    const GROUP_SHARE_ENTRY = 85;
    const GROUP_DELETE = 89;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $activityType;

    /**
     * @var Account
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $primaryObjectType;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $primaryObjectId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $secondaryObjectType;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $secondaryObjectId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    public function __construct($activityType, $primaryObjectType, $primaryObjectId)
    {
        $this->activityType = $activityType;
        $this->primaryObjectType = $primaryObjectType;
        $this->primaryObjectId = $primaryObjectId;
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getActivityType()
    {
        return $this->activityType;
    }

    /**
     * @param int $activityType
     * @return Activity
     */
    public function setActivityType($activityType)
    {
        $this->activityType = $activityType;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Activity
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrimaryObjectId()
    {
        return $this->primaryObjectId;
    }

    /**
     * @param $primaryObjectId
     * @return Activity
     */
    public function setPrimaryObjectId($primaryObjectId)
    {
        $this->primaryObjectId = $primaryObjectId;
        return $this;
    }

    /**
     * @return Account
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Account $user
     * @return Activity
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrimaryObjectType()
    {
        return $this->primaryObjectType;
    }

    /**
     * @param int $primaryObjectType
     * @return Activity
     */
    public function setPrimaryObjectType($primaryObjectType)
    {
        $this->primaryObjectType = $primaryObjectType;
        return $this;
    }

    /**
     * @return int
     */
    public function getSecondaryObjectType()
    {
        return $this->secondaryObjectType;
    }

    /**
     * @param int $secondaryObjectType
     * @return Activity
     */
    public function setSecondaryObjectType($secondaryObjectType)
    {
        $this->secondaryObjectType = $secondaryObjectType;
        return $this;
    }

    /**
     * @return int
     */
    public function getSecondaryObjectId()
    {
        return $this->secondaryObjectId;
    }

    /**
     * @param int $secondaryObjectId
     * @return Activity
     */
    public function setSecondaryObjectId($secondaryObjectId)
    {
        $this->secondaryObjectId = $secondaryObjectId;
        return $this;
    }
}
