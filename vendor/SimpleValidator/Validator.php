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
class Validator
{
    private $data = array();
    private $errors = array();
    private $validators = array();


    public function __construct(array $data, array $validators)
    {
        $this->data = $data;
        $this->validators = $validators;
    }


    public function execute()
    {
        $result = true;

        foreach ($this->validators as $validator) {

            if (! $validator->execute($this->data)) {

                $this->addError(
                    $validator->getField(),
                    $validator->getErrorMessage()
                );

                $result = false;
            }
        }

        return $result;
    }


    public function addError($field, $message)
    {
        if (! isset($this->errors[$field])) {

            $this->errors[$field] = array();
        }

        $this->errors[$field][] = $message;
    }


    public function getErrors()
    {
        return $this->errors;
    }
}