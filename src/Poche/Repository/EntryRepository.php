<?php

namespace Poche\Repository;

class EntryRepository
{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getEntries() {
        $sql = "SELECT * FROM entries";
        $entries = $this->db->fetchAssoc($sql);
        return ($entries ? $entries : array());
    }


}

