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
        if ($query == false) {
            die(STORAGE . ' database looks empty. You have to create it (you can find database structure in install folder).');
        }
        $hasAdmin = count($query->fetchAll());

        if ($hasAdmin == 0) 
            return false;

        return true;
    }

    public function checkTags() {

        if (STORAGE == 'sqlite') {
            $sql = '
                CREATE TABLE IF NOT EXISTS tags (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
                    value TEXT
                )';
        }
        elseif(STORAGE == 'mysql') {
            $sql = '
                CREATE TABLE IF NOT EXISTS `tags` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `value` varchar(255) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ';
        }
        else {
            $sql = '
                CREATE TABLE tags (
                  id bigserial primary key,
                  value varchar(255) NOT NULL
                );
            ';
        }

        $query = $this->executeQuery($sql, array());

        if (STORAGE == 'sqlite') {
            $sql = '
                CREATE TABLE tags_entries (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
                    entry_id INTEGER,
                    tag_id INTEGER,
                    FOREIGN KEY(entry_id) REFERENCES entries(id) ON DELETE CASCADE,
                    FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE
                )';
        }
        elseif(STORAGE == 'mysql') {
            $sql = '
                CREATE TABLE IF NOT EXISTS `tags_entries` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `entry_id` int(11) NOT NULL,
                  `tag_id` int(11) NOT NULL,
                  FOREIGN KEY(entry_id) REFERENCES entries(id) ON DELETE CASCADE,
                  FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ';
        }
        else {
            $sql = '
                CREATE TABLE tags_entries (
                  id bigserial primary key,
                  entry_id integer NOT NULL,
                  tag_id integer NOT NULL
                )
            ';
        }

        $query = $this->executeQuery($sql, array());
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
        $params = array($id_user, 'pager', PAGINATION);
        $query = $this->executeQuery($sql, $params);

        $sql = 'INSERT INTO users_config ( user_id, name, value ) VALUES (?, ?, ?)';
        $params = array($id_user, 'language', LANG);
        $query = $this->executeQuery($sql, $params);
        
        $sql = 'INSERT INTO users_config ( user_id, name, value ) VALUES (?, ?, ?)';
        $params = array($id_user, 'theme', DEFAULT_THEME);
        $query = $this->executeQuery($sql, $params);

        return TRUE;
    }

    public function getConfigUser($id) {
        $sql = "SELECT * FROM users_config WHERE user_id = ?";
        $query = $this->executeQuery($sql, array($id));
        $result = $query->fetchAll();
        $user_config = array();
        
        foreach ($result as $key => $value) {
            $user_config[$value['name']] = $value['value'];
        }

        return $user_config;
    }

    public function userExists($username) {
        $sql = "SELECT * FROM users WHERE username=?";
        $query = $this->executeQuery($sql, array($username));
        $login = $query->fetchAll();
        if (isset($login[0])) {
            return true;
        } else {
            return false;
        }
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

    public function updatePassword($userId, $password)
    {
        $sql_update = "UPDATE users SET password=? WHERE id=?";
        $params_update = array($password, $userId);
        $query = $this->executeQuery($sql_update, $params_update);
    }
    
    public function updateUserConfig($userId, $key, $value) {
        $config = $this->getConfigUser($userId);
        
        if (!isset ($user_config[$key])) {
            $sql = "INSERT INTO users_config (value, user_id, name) VALUES (?, ?, ?)";
        }
        else {
            $sql = "UPDATE users_config SET value=? WHERE user_id=? AND name=?";
        }

        $params = array($value, $userId, $key);
        $query = $this->executeQuery($sql, $params);
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

        return isset($entry[0]) ? $entry[0] : null;
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

    public function retrieveAllTags() {
        $sql = "SELECT * FROM tags";
        $query = $this->executeQuery($sql, array());
        $tags = $query->fetchAll();

        return $tags;
    }

    public function retrieveTag($id) {
        $tag  = NULL;
        $sql    = "SELECT * FROM tags WHERE id=?";
        $params = array(intval($id));
        $query  = $this->executeQuery($sql, $params);
        $tag  = $query->fetchAll();

        return isset($tag[0]) ? $tag[0] : null;
    }

    public function retrieveEntriesByTag($tag_id) {
        $sql = 
            "SELECT * FROM entries
            LEFT JOIN tags_entries ON tags_entries.entry_id=entries.id
            WHERE tags_entries.tag_id = ?";
        $query = $this->executeQuery($sql, array($tag_id));
        $entries = $query->fetchAll();

        return $entries;
    }

    public function retrieveTagsByEntry($entry_id) {
        $sql = 
            "SELECT * FROM tags
            LEFT JOIN tags_entries ON tags_entries.tag_id=tags.id
            WHERE tags_entries.entry_id = ?";
        $query = $this->executeQuery($sql, array($entry_id));
        $tags = $query->fetchAll();

        return $tags;
    }

    public function removeTagForEntry($entry_id, $tag_id) {
        $sql_action     = "DELETE FROM tags_entries WHERE tag_id=? AND entry_id=?";
        $params_action  = array($tag_id, $entry_id);
        $query          = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    public function retrieveTagByValue($value) {
        $tag  = NULL;
        $sql    = "SELECT * FROM tags WHERE value=?";
        $params = array($value);
        $query  = $this->executeQuery($sql, $params);
        $tag  = $query->fetchAll();

        return isset($tag[0]) ? $tag[0] : null;
    }

    public function createTag($value) {
        $sql_action = 'INSERT INTO tags ( value ) VALUES (?)';
        $params_action = array($value);
        $query = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    public function setTagToEntry($tag_id, $entry_id) {
        $sql_action = 'INSERT INTO tags_entries ( tag_id, entry_id ) VALUES (?, ?)';
        $params_action = array($tag_id, $entry_id);
        $query = $this->executeQuery($sql_action, $params_action);
        return $query;
    }
}
