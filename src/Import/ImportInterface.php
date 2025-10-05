<?php

namespace Wallabag\Import;

use Psr\Log\LoggerAwareInterface;
use Wallabag\Entity\User;

interface ImportInterface extends LoggerAwareInterface
{
    /**
     * Name of the import.
     *
     * @return string
     */
    public function getName();

    /**
     * Url to start the import.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Description of the import.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Import content using the user token.
     *
     * @return bool
     */
    public function import();

    /**
     * Return an array with summary info about the import, with keys:
     *     - skipped
     *     - imported.
     *
     * @return array
     */
    public function getSummary();

    /**
     * Set current user.
     * Could the current *connected* user or one retrieve by the consumer.
     */
    public function setUser(User $user);

    /**
     * Set file path to the json file.
     *
     * @param string $filepath
     */
    public function setFilepath($filepath): static;

    /**
     * Set whether articles must be all marked as read.
     *
     * @param bool $markAsRead
     */
    public function setMarkAsRead($markAsRead): static;
}
