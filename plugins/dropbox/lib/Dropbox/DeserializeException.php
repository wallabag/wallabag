<?php
namespace Dropbox;

/**
 * If, when loading a serialized {@link RequestToken} or {@link AccessToken}, the input string is
 * malformed, this exception will be thrown.
 */
final class DeserializeException extends \Exception
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
