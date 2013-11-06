<?php
namespace Dropbox;

/**
 * Thrown when there's an error reading from a stream that was passed in by the caller.
 */
class StreamReadException extends \Exception
{
    /**
     * @internal
     */
    function __construct($message, $cause = null)
    {
        parent::__construct($message, 0, $cause);
    }
}
