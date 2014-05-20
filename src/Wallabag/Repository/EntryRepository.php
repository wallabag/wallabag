<?php

namespace Wallabag\Repository;

class EntryRepository
{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    //TODO don't hardcode the user ;)
    public function getEntries($status, $userId = 1) {
        $sql = "SELECT * FROM entries where user_id = ? AND status = ? ORDER BY id DESC";
        $entries = $this->db->fetchAll($sql, array($userId, $status));

        return $entries ? $entries : array();
    }

    //TODO don't hardcode the user ;)
    public function getBookmarks($userId = 1) {
        $sql = "SELECT * FROM entries where user_id = ? AND bookmark = 1 and status <> 'removed' ORDER BY id DESC";
        $entries = $this->db->fetchAll($sql, array($userId));

        return $entries ? $entries : array();
    }

    //TODO don't hardcode the user ;)
    public function saveEntry($entry, $userId = 1) {

        return $this->db->insert('entries', array_merge($entry, array('user_id' => $userId, 'status' => 'unread')));
    }

    //TODO don't hardcode the user ;)
    public function getEntryById($id, $userId = 1) {
        $sql = "SELECT * FROM entries where id = ? AND user_id = ? AND status <> 'removed'";
        $entry = $this->db->fetchAll($sql, array($id, $userId));

        return $entry ? $entry : array();
    }

    //TODO don't hardcode the user ;)
    public function markAsRead($id, $userId = 1) {
        $sql = "UPDATE entries SET status = 'read' where id = ? AND user_id = ?";
        $count = $this->db->executeUpdate($sql, array($id, $userId));

        return $count;
    }

    //TODO don't hardcode the user ;)
    public function markAsUnread($id, $userId = 1) {
        $sql = "UPDATE entries SET status = 'unread' where id = ? AND user_id = ?";
        $count = $this->db->executeUpdate($sql, array($id, $userId));

        return $count;
    }

    //TODO don't hardcode the user ;)
    public function star($id, $userId = 1) {
        $sql = "UPDATE entries SET bookmark = 1 where id = ? AND user_id = ?";
        $count = $this->db->executeUpdate($sql, array($id, $userId));

        return $count;
    }

    //TODO don't hardcode the user ;)
    public function unstar($id, $userId = 1) {
        $sql = "UPDATE entries SET bookmark = 0 where id = ? AND user_id = ?";
        $count = $this->db->executeUpdate($sql, array($id, $userId));

        return $count;
    }

    //TODO don't hardcode the user ;)
    public function remove($id, $userId = 1) {
        $sql = "UPDATE entries SET status = 'removed' where id = ? AND user_id = ?";
        $count = $this->db->executeUpdate($sql, array($id, $userId));

        return $count;
    }

    //TODO don't hardcode the user ;)
    public function restore($id, $userId = 1) {
        $sql = "UPDATE entries SET status = 'unread' where id = ? AND user_id = ?";
        $count = $this->db->executeUpdate($sql, array($id, $userId));

        return $count;
    }

}

