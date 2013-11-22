<?php

namespace Poche\Api;

class EntryApi
{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getEntries() {
        //Todo
        return array();
    }
}
