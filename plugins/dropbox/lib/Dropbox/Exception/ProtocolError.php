<?php
namespace Dropbox;

/**
 * There was an protocol misunderstanding between this SDK and the server.  One of us didn't
 * understand what the other one was saying.
 */
class Exception_ProtocolError extends Exception
{
    /**
     * @internal
     */
    function __construct($message)
    {
        parent::__construct($message);
    }
}
