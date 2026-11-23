<?php

namespace Wallabag\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class HttpUrlWithoutCredentials extends Constraint
{
    public string $message = 'This value is not a valid URL.';
}
