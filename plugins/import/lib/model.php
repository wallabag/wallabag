<?php

namespace Import;

use SimpleValidator\Validator;
use SimpleValidator\Validators;

function validate_import(array $values)
{
    $v = new Validator($values, array(
        new Validators\Required('application', t('You have to choose an application')),
    ));

    return array(
        $v->execute(),
        $v->getErrors()
    );
}
