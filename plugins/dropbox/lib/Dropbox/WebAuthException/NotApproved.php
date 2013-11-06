<?php
namespace Dropbox;

/**
 * Thrown if the user chose not to grant your app access to their Dropbox account.
 */
class WebAuthException_NotApproved extends \Exception
{
    /**
     * @param string $message
     *
     * @internal
     */
    function __construct($message)
    {
        parent::__construct($message);
    }
}
