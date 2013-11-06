<?php
namespace Dropbox;

/**
 * There was a network I/O error when making the request.
 */
final class Exception_NetworkIO extends Exception
{
    /**
     * @internal
     */
    function __construct($message, $cause = null)
    {
        parent::__construct($message, $cause);
    }
}
