<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Wallabag\CoreBundle\Notifications\ActionInterface;
use Wallabag\CoreBundle\Notifications\NotificationInterface;
use Wallabag\UserBundle\Entity\User;

/**
 * Class Notification.
 *
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\NotificationRepository")
 * @ORM\Table(name="`notification`")
 */
class Notification implements NotificationInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer")
     */
    protected $type;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Wallabag\UserBundle\Entity\User", inversedBy="notifications")
     */
    protected $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime")
     */
    protected $timestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string")
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    protected $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="read", type="boolean")
     */
    protected $read;

    /**
     * @var array
     *
     * @ORM\Column(name="parameters", type="array", nullable=true)
     */
    protected $parameters;

    protected $logger;

    /**
     * @var ArrayCollection<ActionInterface>
     *
     * @ORM\Column(name="actions", type="array", nullable=true)
     */
    protected $actions;

    protected $actionTypes = [];

    const TYPE_ADMIN = 0;
    const TYPE_USER = 1;
    const TYPE_RELEASE = 2;

    public function __construct(User $user = null)
    {
        $this->logger = new NullLogger();
        $this->timestamp = new \DateTime();
        $this->actions = new ArrayCollection();
        $this->parameters = [];
        $this->read = false;
        $this->user = $user;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return NotificationInterface
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return NotificationInterface
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
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
     *
     * @return NotificationInterface
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     *
     * @return NotificationInterface
     */
    public function setTimestamp(\DateTime $timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return NotificationInterface
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRead()
    {
        return $this->read;
    }

    /**
     * @param bool $read
     *
     * @return NotificationInterface
     */
    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * @param ActionInterface $action
     *
     * @return NotificationInterface
     *
     * @throws \InvalidArgumentException
     */
    public function addAction(ActionInterface $action)
    {
        if (isset($this->actionTypes[$action->getType()])) {
            throw new \InvalidArgumentException('The notification already has a primary action');
        }
        $this->actionTypes[$action->getType()] = true;
        $this->actions->add($action);

        return $this;
    }

    /**
     * @return ArrayCollection<ActionInterface>
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Notification
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return Notification
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return Notification
     *
     * @throws \InvalidArgumentException
     */
    public function addParameter($key, $value)
    {
        if (in_array($key, $this->parameters, true)) {
            throw new \InvalidArgumentException('This parameter already is set');
        }

        $this->parameters[$key] = $value;

        return $this;
    }
}
