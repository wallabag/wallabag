<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

class Sqlite extends Store {

    public static $db_path = 'sqlite:./db/poche.sqlite';
    var $handle;

    function __construct() {
        parent::__construct();

        $this->handle = new PDO(self::$db_path);
        $this->handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function getHandle() {
        return $this->handle;
    }

    public function isInstalled() {
        $sql = "SELECT username FROM users WHERE id=?";
        $query = $this->executeQuery($sql, array('1'));
        $hasAdmin = $query->fetchAll();

        if (count($hasAdmin) == 0) 
            return FALSE;

        return TRUE;
    }

    public function install($login, $password) {
        $sql = 'INSERT INTO users ( username, password ) VALUES (?, ?)';
        $params = array($login, $password);
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
        $sql    = "SELECT * FROM users WHERE username=? AND password=?";
        $query  = $this->executeQuery($sql, array($username, $password));
        $login  = $query->fetchAll();

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
        $sql_update     = "UPDATE users SET password=? WHERE id=?";
        $params_update  = array($password, $id);
        $query          = $this->executeQuery($sql_update, $params_update);
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
        }
    }

    public function retrieveAll() {
        $sql        = "SELECT * FROM entries ORDER BY id";
        $query      = $this->executeQuery($sql, array());
        $entries    = $query->fetchAll();

        return $entries;
    }

    public function retrieveOneById($id) {
        parent::__construct();

        $entry  = NULL;
        $sql    = "SELECT * FROM entries WHERE id=?";
        $params = array(intval($id));
        $query  = $this->executeQuery($sql, $params);
        $entry  = $query->fetchAll();

        return $entry[0];
    }

    public function getEntriesByView($view, $limit = '') {
        parent::__construct();

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
                $sql    = "SELECT * FROM entries WHERE is_read=? " . $order;
                $params = array(-1);
                break;
            case 'fav' :
                $sql    = "SELECT * FROM entries WHERE is_fav=? " . $order;
                $params = array(-1);
                break;
            default:
                $sql    = "SELECT * FROM entries WHERE is_read=? " . $order;
                $params = array(0);
                break;
        }

        $sql .= ' ' . $limit;

        $query      = $this->executeQuery($sql, $params);
        $entries    = $query->fetchAll();

        return $entries;
    }

    public function add($url, $title, $content) {
        parent::__construct();
        $sql_action     = 'INSERT INTO entries ( url, title, content ) VALUES (?, ?, ?)';
        $params_action  = array($url, $title, $content);
        $query          = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    public function deleteById($id) {
        parent::__construct();
        $sql_action     = "DELETE FROM entries WHERE id=?";
        $params_action  = array($id);
        $query          = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    public function favoriteById($id) {
        parent::__construct();
        $sql_action     = "UPDATE entries SET is_fav=~is_fav WHERE id=?";
        $params_action  = array($id);
        $query          = $this->executeQuery($sql_action, $params_action);
    }

    public function archiveById($id) {
        parent::__construct();
        $sql_action     = "UPDATE entries SET is_read=~is_read WHERE id=?";
        $params_action  = array($id);
        $query          = $this->executeQuery($sql_action, $params_action);
    }

    public function getLastId() {
        parent::__construct();
        return $this->getHandle()->lastInsertId();
    }

    public function updateContentById($id) {
        parent::__construct();
        $sql_update     = "UPDATE entries SET content=? WHERE id=?";
        $params_update  = array($content, $id);
        $query          = $this->executeQuery($sql_update, $params_update);
    }
}
