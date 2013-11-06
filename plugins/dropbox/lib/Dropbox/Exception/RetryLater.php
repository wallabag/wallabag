<?php
namespace Dropbox;

/**
 * The Dropbox server said it couldn't fulfil our request right now, but that we should try
 * again later.
 */
final class Exception_RetryLater extends Exception
{
    /**
     * @internal
     */
    function __construct($message)
    {
        parent::__construct($message);
    }
}
