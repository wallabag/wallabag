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
        $sql = "SELECT * FROM entries where user_id = :userId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $entries = $stmt->fetchAll();
        return $entries;
    }

    //TODO don't hardcode the user ;)
    public function saveEntry($entry, $userId = 1) {

        $sql = "INSERT INTO entries (url, title, content, user_id) values (:url, :title, :content, :user_id)";
        return $this->db->insert('entries', array_merge($entry, array('user_id' => $userId)));
    }
}

