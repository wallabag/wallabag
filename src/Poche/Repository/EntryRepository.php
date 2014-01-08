<?php

namespace Poche\Repository;

class EntryRepository
{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    //TODO don't hardcode the user ;)
    public function getEntries($userId = 1) {
        $sql = "SELECT * FROM entries where user_id = ? ORDER BY id DESC";
        $entries = $this->db->fetchAll($sql, array($userId));

        return $entries ? $entries : array();
    }

    //TODO don't hardcode the user ;)
    public function saveEntry($entry, $userId = 1) {

        return $this->db->insert('entries', array_merge($entry, array('user_id' => $userId)));
    }

    //TODO don't hardcode the user ;)
    public function getEntryById($id, $userId = 1) {
        $sql = "SELECT * FROM entries where id = ? AND user_id = ?";
        $entry = $this->db->fetchAll($sql, array($id, $userId));

        return $entry ? $entry[0] : array();
    }
}

