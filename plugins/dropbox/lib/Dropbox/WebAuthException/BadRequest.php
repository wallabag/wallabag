<?php
namespace Dropbox;

/**
 * Thrown if the redirect URL was missing parameters or if the given parameters were not valid.
 *
 * The recommended action is to show an HTTP 400 error page.
 */
class WebAuthException_BadRequest extends \Exception
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
