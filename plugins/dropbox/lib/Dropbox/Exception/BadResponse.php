<?php
namespace Dropbox;

/**
 * When this SDK can't understand the response from the server.  This could be due to a bug in this
 * SDK or a buggy response from the Dropbox server.
 */
class Exception_BadResponse extends Exception_ProtocolError
{
    /**
     * @internal
     */
    function __construct($message)
    {
        parent::__construct($message);
    }
}
