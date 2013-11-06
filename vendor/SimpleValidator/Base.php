<?php

/*
 * This file is part of Simple Validator.
 *
 * (c) Frédéric Guillot <contact@fredericguillot.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SimpleValidator;

/**
 * @author Frédéric Guillot <contact@fredericguillot.com>
 */
abstract class Base
{
    protected $field = '';
    protected $error_message = '';
    protected $data = array();


    abstract public function execute(array $data);


    public function __construct($field, $error_message)
    {
        $this->field = $field;
        $this->error_message = $error_message;
    }


    public function getErrorMessage()
    {
        return $this->error_message;
    }


    public function getField()
    {
        return $this->field;
    }
}