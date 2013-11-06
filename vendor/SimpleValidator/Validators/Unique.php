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
class Unique extends Base
{
    private $pdo;
    private $primary_key;
    private $table;


    public function __construct($field, $error_message, \PDO $pdo, $table, $primary_key = 'id')
    {
        parent::__construct($field, $error_message);

        $this->pdo = $pdo;
        $this->primary_key = $primary_key;
        $this->table = $table;
    }


    public function execute(array $data)
    {
        if (isset($data[$this->field]) && $data[$this->field] !== '') {

            if (! isset($data[$this->primary_key])) {

                $rq = $this->pdo->prepare('SELECT COUNT(*) FROM '.$this->table.' WHERE '.$this->field.'=?');

                $rq->execute(array(
                    $data[$this->field]
                ));

                $result = $rq->fetch(\PDO::FETCH_NUM);

                if (isset($result[0]) && $result[0] === '1') {

                    return false;
                }
            }
            else {

                $rq = $this->pdo->prepare(
                    'SELECT COUNT(*) FROM '.$this->table.'
                    WHERE '.$this->field.'=? AND '.$this->primary_key.' != ?'
                );
                
                $rq->execute(array(
                    $data[$this->field], 
                    $data[$this->primary_key]
                ));
                
                $result = $rq->fetch(\PDO::FETCH_NUM);

                if (isset($result[0]) && $result[0] === '1') {

                    return false;
                }
            }
        }

        return true;
    }
}