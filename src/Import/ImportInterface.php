<?php

namespace Wallabag\Import;

use Psr\Log\LoggerAwareInterface;

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
}
