<?php
namespace Dropbox;

/**
 * Thrown when the server tells us that our request was invalid.  This is typically due to an
 * HTTP 400 response from the server.
 */
final class Exception_BadRequest extends Exception_ProtocolError
{
    /**
     * @internal
     */
    function __construct($message = "")
    {
        parent::__construct($message);
    }
}
