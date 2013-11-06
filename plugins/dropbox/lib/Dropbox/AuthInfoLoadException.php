<?php
namespace Dropbox;

/**
 * Thrown by the <code>AuthInfo::loadXXX</code> methods if something goes wrong.
 */
final class AuthInfoLoadException extends \Exception
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
