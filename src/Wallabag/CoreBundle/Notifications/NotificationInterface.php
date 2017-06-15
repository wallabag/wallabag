<?php

namespace Wallabag\CoreBundle\Notifications;

use Psr\Log\LoggerAwareInterface;

interface NotificationInterface extends LoggerAwareInterface
{
    /**
     * Title of the notification.
     *
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     *
     * @return NotificationInterface
     */
    public function setTitle($title);

    /**
     * Type of the notification.
     *
     * @return string
     */
    public function getType();

    /**
     * @param int $type
     *
     * @return NotificationInterface
     */
    public function setType($type);

    /**
     * If the notification has been viewed / dismissed or not.
     *
     * @return bool
     */
    public function isRead();

    /**
     * @param bool $read
     *
     * @return NotificationInterface
     */
    public function setRead($read);

    /**
     * When the notification was sent.
     *
     * @return \DateTime
     */
    public function getTimestamp();

    /**
     * @param \DateTime $timestamp
     *
     * @return NotificationInterface
     */
    public function setTimestamp(\DateTime $timestamp);

    /**
     * @param ActionInterface $action
     *
     * @return NotificationInterface
     */
    public function addAction(ActionInterface $action);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return NotificationInterface
     */
    public function setDescription($description);

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @param array $parameters
     *
     * @return NotificationInterface
     */
    public function setParameters($parameters);

    /**
     * @param string $key
     * @param string $value
     *
     * @return NotificationInterface
     */
    public function addParameter($key, $value);
}
