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
class Length extends Base
{
    private $min;
    private $max;


    public function __construct($field, $error_message, $min, $max)
    {
        parent::__construct($field, $error_message);

        $this->min = $min;
        $this->max = $max;
    }


    public function execute(array $data)
    {
        if (isset($data[$this->field]) && $data[$this->field] !== '') {

            $length = mb_strlen($data[$this->field], 'UTF-8');

            if ($length < $this->min || $length > $this->max) {

                return false;
            }
        }

        return true;
    }
}