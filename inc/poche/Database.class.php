<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

class Database {
    var $handle;

    function __construct()
    {
        switch (STORAGE) {
            case 'sqlite':
                $db_path = 'sqlite:' . STORAGE_SQLITE;
                $this->handle = new PDO($db_path);
                break;
            case 'mysql':
                $db_path = 'mysql:host=' . STORAGE_SERVER . ';dbname=' . STORAGE_DB;
                $this->handle = new PDO($db_path, STORAGE_USER, STORAGE_PASSWORD); 
                break;
            case 'postgres':
                $db_path = 'pgsql:host=' . STORAGE_SERVER . ';dbname=' . STORAGE_DB;
                $this->handle = new PDO($db_path, STORAGE_USER, STORAGE_PASSWORD); 
                break;
        }

        $this->handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        Tools::logm('storage type ' . STORAGE);
    }

    private function getHandle() {
        return $this->handle;
    }

    public function isInstalled() {
        $sql = "SELECT username FROM users";
        $query = $this->executeQuery($sql, array());
        $hasAdmin = count($query->fetchAll());

        if ($hasAdmin == 0) 
            return FALSE;

        return TRUE;
    }

    public function install($login, $password) {
        $sql = 'INSERT INTO users ( username, password, name, email) VALUES (?, ?, ?, ?)';
        $params = array($login, $password, $login, ' ');
        $query = $this->executeQuery($sql, $params);

        $sequence = '';
        if (STORAGE == 'postgres') {
            $sequence = 'users_id_seq';
        }

        $id_user = intval($this->getLastId($sequence));

        $sql = 'INSERT INTO users_config ( user_id, name, value ) VALUES (?, ?, ?)';
        $params = array($id_user, 'pager', '10');
        $query = $this->executeQuery($sql, $params);

        $sql = 'INSERT INTO users_config ( user_id, name, value ) VALUES (?, ?, ?)';
        $params = array($id_user, 'language', 'en_EN.UTF8');
        $query = $this->executeQuery($sql, $params);

        return TRUE;
    }

    private function getConfigUser($id) {
        $sql = "SELECT * FROM users_config WHERE user_id = ?";
        $query = $this->executeQuery($sql, array($id));
        $result = $query->fetchAll();
        $user_config = array();
        
        foreach ($result as $key => $value) {
            $user_config[$value['name']] = $value['value'];
        }

        return $user_config;
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username=? AND password=?";
        $query = $this->executeQuery($sql, array($username, $password));
        $login = $query->fetchAll();

        $user = array();
        if (isset($login[0])) {
            $user['id'] = $login[0]['id'];
            $user['username'] = $login[0]['username'];
            $user['password'] = $login[0]['password'];
            $user['name'] = $login[0]['name'];
            $user['email'] = $login[0]['email'];
            $user['config'] = $this->getConfigUser($login[0]['id']);
        }

        return $user;
    }

    public function updatePassword($id, $password)
    {
        $sql_update = "UPDATE users SET password=? WHERE id=?";
        $params_update = array($password, $id);
        $query = $this->executeQuery($sql_update, $params_update);
    }

    private function executeQuery($sql, $params) {
        try
        {
            $query = $this->getHandle()->prepare($sql);
            $query->execute($params);
            return $query;
        }
        catch (Exception $e)
        {
            Tools::logm('execute query error : '.$e->getMessage());
            return FALSE;
        }
    }

    public function retrieveAll($user_id) {
        $sql        = "SELECT * FROM entries WHERE user_id=? ORDER BY id";
        $query      = $this->executeQuery($sql, array($user_id));
        $entries    = $query->fetchAll();

        return $entries;
    }

    public function retrieveOneById($id, $user_id) {
        $entry  = NULL;
        $sql    = "SELECT * FROM entries WHERE id=? AND user_id=?";
        $params = array(intval($id), $user_id);
        $query  = $this->executeQuery($sql, $params);
        $entry  = $query->fetchAll();

        return $entry[0];
    }

    public function getEntriesByView($view, $user_id, $limit = '') {
        switch ($_SESSION['sort'])
        {
            case 'ia':
                $order = 'ORDER BY id';
                break;
            case 'id':
                $order = 'ORDER BY id DESC';
                break;
            case 'ta':
                $order = 'ORDER BY lower(title)';
                break;
            case 'td':
                $order = 'ORDER BY lower(title) DESC';
                break;
            default:
                $order = 'ORDER BY id';
                break;
        }

        switch ($view)
        {
            case 'archive':
                $sql    = "SELECT * FROM entries WHERE user_id=? AND is_read=? " . $order;
                $params = array($user_id, 1);
                break;
            case 'fav' :
                $sql    = "SELECT * FROM entries WHERE user_id=? AND is_fav=? " . $order;
                $params = array($user_id, 1);
                break;
            default:
                $sql    = "SELECT * FROM entries WHERE user_id=? AND is_read=? " . $order;
                $params = array($user_id, 0);
                break;
        }

        $sql .= ' ' . $limit;

        $query = $this->executeQuery($sql, $params);
        $entries = $query->fetchAll();

        return $entries;
    }

    public function updateContent($id, $content, $user_id) {
        $sql_action = 'UPDATE entries SET content = ? WHERE id=? AND user_id=?';
        $params_action = array($content, $id, $user_id);
        $query = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    public function add($url, $title, $content, $user_id) {
        $sql_action = 'INSERT INTO entries ( url, title, content, user_id ) VALUES (?, ?, ?, ?)';
        $params_action = array($url, $title, $content, $user_id);
        $query = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    public function deleteById($id, $user_id) {
        $sql_action     = "DELETE FROM entries WHERE id=? AND user_id=?";
        $params_action  = array($id, $user_id);
        $query          = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    public function favoriteById($id, $user_id) {
        $sql_action     = "UPDATE entries SET is_fav=NOT is_fav WHERE id=? AND user_id=?";
        $params_action  = array($id, $user_id);
        $query          = $this->executeQuery($sql_action, $params_action);
    }

    public function archiveById($id, $user_id) {
        $sql_action     = "UPDATE entries SET is_read=NOT is_read WHERE id=? AND user_id=?";
        $params_action  = array($id, $user_id);
        $query          = $this->executeQuery($sql_action, $params_action);
    }

    public function getLastId($column = '') {
        return $this->getHandle()->lastInsertId($column);
    }
}
