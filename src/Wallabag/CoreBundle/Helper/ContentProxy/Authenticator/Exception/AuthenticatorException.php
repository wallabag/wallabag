<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\Exception;

use Exception;

class AuthenticatorException extends Exception
{
    public function __construct($message, $uri = 'n/a')
    {
        parent::__construct($message.'. Uri: '.$uri, 0);
    }
}
