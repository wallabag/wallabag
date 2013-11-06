<?php
namespace Dropbox;

/**
 * Thrown if all the parameters are correct, but there's no CSRF token in the session.  This
 * probably means that the session expired.
 *
 * The recommended action is to redirect the user's browser to try the approval process again.
 */
class WebAuthException_BadState extends \Exception
{
    /**
     * @param string $message
     *
     * @internal
     */
    function __construct()
    {
        parent::__construct("Missing CSRF token in session.");
    }
}
