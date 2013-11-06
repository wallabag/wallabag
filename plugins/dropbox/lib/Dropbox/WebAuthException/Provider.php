<?php
namespace Dropbox;

/**
 * Thrown if Dropbox returns some other error about the authorization request.
 */
class WebAuthException_Provider extends \Exception
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
