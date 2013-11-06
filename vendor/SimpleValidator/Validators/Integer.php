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
class Integer extends Base
{
    public function execute(array $data)
    {
        if (isset($data[$this->field]) && $data[$this->field] !== '') {

            if (is_string($data[$this->field])) {

                if ($data[$this->field][0] === '-') {

                    return ctype_digit(substr($data[$this->field], 1));
                }

                return ctype_digit($data[$this->field]);
            }
            else {

                return is_int($data[$this->field]);
            }           
        }

        return true;
    }
}