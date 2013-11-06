<?php
namespace Dropbox;

/**
 * Thrown by the <code>AppInfo::loadXXX</code> methods if something goes wrong.
 */
final class AppInfoLoadException extends \Exception
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
