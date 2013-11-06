<?php

/*
 * This file is part of Simple Validator.
 *
 * (c) Frédéric Guillot <contact@fredericguillot.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SimpleValidator\Validators;

use SimpleValidator\Base;

/**
 * @author Frédéric Guillot <contact@fredericguillot.com>
 */
class Equals extends Base
{
    private $field2;


    public function __construct($field1, $field2, $error_message)
    {
        parent::__construct($field1, $error_message);

        $this->field2 = $field2;
    }


    public function execute(array $data)
    {
        if (isset($data[$this->field]) && $data[$this->field] !== '') {

            if (! isset($data[$this->field2])) return false;

            return $data[$this->field] === $data[$this->field2];
        }

        return true;
    }
}