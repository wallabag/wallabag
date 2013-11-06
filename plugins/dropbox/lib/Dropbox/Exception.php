<?php
namespace Dropbox;

/**
 * The base class for all API call exceptions.
 */
class Exception extends \Exception
{
    /**
     * @internal
     */
    function __construct($message, $cause = null)
    {
        parent::__construct($message, 0, $cause);
    }
}
