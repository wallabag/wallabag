<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

class Database {

    var $handle;
    private $order;
/*    
    private $order = array (
        'ia' => 'ORDER BY '.STORAGE_PREFIX.'entries.id',
        'id' => 'ORDER BY '.STORAGE_PREFIX.'entries.id DESC',
        'ta' => 'ORDER BY lower('.STORAGE_PREFIX.'entries.title)',
        'td' => 'ORDER BY lower('.STORAGE_PREFIX.'entries.title) DESC',
        'default' => 'ORDER BY '.STORAGE_PREFIX.'entries.id'
    );
*/

    function __construct()
    {
        $order['ia'] = 'ORDER BY '.STORAGE_PREFIX.'entries.id';
        $order['id'] = 'ORDER BY '.STORAGE_PREFIX.'entries.id DESC';
        $order['ta'] = 'ORDER BY lower('.STORAGE_PREFIX.'entries.title)';
        $order['td'] = 'ORDER BY lower('.STORAGE_PREFIX.'entries.title) DESC';
        $order['default'] = 'ORDER BY '.STORAGE_PREFIX.'entries.id';
    
        switch (STORAGE) {
            case 'sqlite':
                // Check if /db is writeable
                if ( !is_writable(STORAGE_SQLITE) || !is_writable(dirname(STORAGE_SQLITE))) {
                	die('An error occured: "db" directory must be writeable for your web server user!');
                }
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
            default:
                die(STORAGE . ' is not a recognised database system !');
        }

        $this->handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_checkTags();
        Tools::logm('storage type ' . STORAGE);
    }

    private function getHandle()
    {
        return $this->handle;
    }

    private function _checkTags()
    {

        if (STORAGE == 'sqlite') {
            $sql = '
                CREATE TABLE IF NOT EXISTS '.STORAGE_PREFIX.'tags (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
                    value TEXT
                )';
        }
        elseif(STORAGE == 'mysql') {
            $sql = '
                CREATE TABLE IF NOT EXISTS `'.STORAGE_PREFIX.'tags` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `value` varchar(255) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ';
        }
        else {
            $sql = '
                CREATE TABLE IF NOT EXISTS '.STORAGE_PREFIX.'tags (
                  id bigserial primary key,
                  value varchar(255) NOT NULL
                );
            ';
        }

        $query = $this->executeQuery($sql, array());

        if (STORAGE == 'sqlite') {
            $sql = '
                CREATE TABLE IF NOT EXISTS '.STORAGE_PREFIX.'tags_entries (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
                    entry_id INTEGER,
                    tag_id INTEGER,
                    FOREIGN KEY(entry_id) REFERENCES '.STORAGE_PREFIX.'entries(id) ON DELETE CASCADE,
                    FOREIGN KEY(tag_id) REFERENCES '.STORAGE_PREFIX.'tags(id) ON DELETE CASCADE
                )';
        }
        elseif(STORAGE == 'mysql') {
            $sql = '
                CREATE TABLE IF NOT EXISTS `tags_entries` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `entry_id` int(11) NOT NULL,
                  `tag_id` int(11) NOT NULL,
                  FOREIGN KEY(entry_id) REFERENCES '.STORAGE_PREFIX.'entries(id) ON DELETE CASCADE,
                  FOREIGN KEY(tag_id) REFERENCES '.STORAGE_PREFIX.'tags(id) ON DELETE CASCADE,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ';
        }
        else {
            $sql = '
                CREATE TABLE IF NOT EXISTS '.STORAGE_PREFIX.'tags_entries (
                  id bigserial primary key,
                  entry_id integer NOT NULL,
                  tag_id integer NOT NULL
                )
            ';
        }

        $query = $this->executeQuery($sql, array());
    }

    public function install($login, $password, $email = '')
    {
        $sql = 'INSERT INTO '.STORAGE_PREFIX.'users ( username, password, name, email) VALUES (?, ?, ?, ?)';
        $params = array($login, $password, $login, $email);
        $query = $this->executeQuery($sql, $params);

        $sequence = '';
        if (STORAGE == 'postgres') {
            $sequence = 'users_id_seq';
        }

        $id_user = intval($this->getLastId($sequence));

        $sql = 'INSERT INTO '.STORAGE_PREFIX.'users_config ( user_id, name, value ) VALUES (?, ?, ?)';
        $params = array($id_user, 'pager', PAGINATION);
        $query = $this->executeQuery($sql, $params);

        $sql = 'INSERT INTO '.STORAGE_PREFIX.'users_config ( user_id, name, value ) VALUES (?, ?, ?)';
        $params = array($id_user, 'language', LANG);
        $query = $this->executeQuery($sql, $params);

        $sql = 'INSERT INTO '.STORAGE_PREFIX.'users_config ( user_id, name, value ) VALUES (?, ?, ?)';
        $params = array($id_user, 'theme', DEFAULT_THEME);
        $query = $this->executeQuery($sql, $params);

        return TRUE;
    }

    public function getConfigUser($id)
    {
        $sql = "SELECT * FROM ".STORAGE_PREFIX."users_config WHERE user_id = ?";
        $query = $this->executeQuery($sql, array($id));
        $result = $query->fetchAll();
        $user_config = array();

        foreach ($result as $key => $value) {
            $user_config[$value['name']] = $value['value'];
        }

        return $user_config;
    }

    public function userExists($username)
    {
        $sql = "SELECT * FROM ".STORAGE_PREFIX."users WHERE username=?";
        $query = $this->executeQuery($sql, array($username));
        $login = $query->fetchAll();
        if (isset($login[0])) {
            return true;
        } else {
            return false;
        }
    }

    public function login($username, $password, $isauthenticated = FALSE)
    {
        if ($isauthenticated) {
            $sql = "SELECT * FROM ".STORAGE_PREFIX."users WHERE username=?";
            $query = $this->executeQuery($sql, array($username));
        } else {
            $sql = "SELECT * FROM ".STORAGE_PREFIX."users WHERE username=? AND password=?";
            $query = $this->executeQuery($sql, array($username, $password));
        }
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
        $sql_update = "UPDATE ".STORAGE_PREFIX."users SET password=? WHERE id=?";
        $params_update = array($password, $userId);
        $query = $this->executeQuery($sql_update, $params_update);
    }

    public function updateUserConfig($userId, $key, $value)
    {
        $config = $this->getConfigUser($userId);

        if (! isset($config[$key])) {
            $sql = "INSERT INTO ".STORAGE_PREFIX."users_config (value, user_id, name) VALUES (?, ?, ?)";
        }
        else {
            $sql = "UPDATE ".STORAGE_PREFIX."users_config SET value=? WHERE user_id=? AND name=?";
        }

        $params = array($value, $userId, $key);
        $query = $this->executeQuery($sql, $params);
    }

    private function executeQuery($sql, $params)
    {
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
    
    public function listUsers($username = NULL)
    {
        $sql = 'SELECT count(*) FROM '.STORAGE_PREFIX.'users'.( $username ? ' WHERE username=?' : '');
        $query = $this->executeQuery($sql, ( $username ? array($username) : array()));
        list($count) = $query->fetch();
        return $count;
    }
    
    public function getUserPassword($userID)
    {
        $sql = "SELECT * FROM ".STORAGE_PREFIX."users WHERE id=?";
        $query = $this->executeQuery($sql, array($userID));
        $password = $query->fetchAll();
        return isset($password[0]['password']) ? $password[0]['password'] : null;
    }
    
    public function deleteUserConfig($userID)
    {
        $sql_action = 'DELETE from '.STORAGE_PREFIX.'users_config WHERE user_id=?';
        $params_action = array($userID);
        $query = $this->executeQuery($sql_action, $params_action);
        return $query;
    }
    
    public function deleteTagsEntriesAndEntries($userID)
    {
        $entries = $this->retrieveAll($userID);
        foreach($entries as $entryid) {
            $tags = $this->retrieveTagsByEntry($entryid);
            foreach($tags as $tag) {
                $this->removeTagForEntry($entryid,$tags);
            }
            $this->deleteById($entryid,$userID);
        }
    }
    
    public function deleteUser($userID)
    {
        $sql_action = 'DELETE from '.STORAGE_PREFIX.'users WHERE id=?';
        $params_action = array($userID);
        $query = $this->executeQuery($sql_action, $params_action);
    }

    public function updateContentAndTitle($id, $title, $body, $user_id)
    {
        $sql_action = 'UPDATE '.STORAGE_PREFIX.'entries SET content = ?, title = ? WHERE id=? AND user_id=?';
        $params_action = array($body, $title, $id, $user_id);
        $query = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    public function retrieveUnfetchedEntries($user_id, $limit)
    {

        $sql_limit = "LIMIT 0,".$limit;
        if (STORAGE == 'postgres') {
            $sql_limit = "LIMIT ".$limit." OFFSET 0";
        }

        $sql        = "SELECT * FROM ".STORAGE_PREFIX."entries WHERE (content = '' OR content IS NULL) AND title LIKE 'Untitled - Import%' AND user_id=? ORDER BY id " . $sql_limit;
        $query      = $this->executeQuery($sql, array($user_id));
        $entries    = $query->fetchAll();

        return $entries;
    }

    public function retrieveUnfetchedEntriesCount($user_id)
    {
      $sql        = "SELECT count(*) FROM ".STORAGE_PREFIX."entries WHERE (content = '' OR content IS NULL) AND title LIKE 'Untitled - Import%' AND user_id=?";
      $query      = $this->executeQuery($sql, array($user_id));
      list($count) = $query->fetch();

      return $count;
    }

    public function retrieveAll($user_id)
    {
        $sql        = "SELECT * FROM ".STORAGE_PREFIX."entries WHERE user_id=? ORDER BY id";
        $query      = $this->executeQuery($sql, array($user_id));
        $entries    = $query->fetchAll();

        return $entries;
    }

    public function retrieveOneById($id, $user_id)
    {
        $entry  = NULL;
        $sql    = "SELECT * FROM ".STORAGE_PREFIX."entries WHERE id=? AND user_id=?";
        $params = array(intval($id), $user_id);
        $query  = $this->executeQuery($sql, $params);
        $entry  = $query->fetchAll();

        return isset($entry[0]) ? $entry[0] : null;
    }

    public function retrieveOneByURL($url, $user_id)
    {
        $entry  = NULL;
        $sql    = "SELECT * FROM ".STORAGE_PREFIX."entries WHERE url=? AND user_id=?";
        $params = array($url, $user_id);
        $query  = $this->executeQuery($sql, $params);
        $entry  = $query->fetchAll();

        return isset($entry[0]) ? $entry[0] : null;
    }

    public function reassignTags($old_entry_id, $new_entry_id)
    {
        $sql    = "UPDATE ".STORAGE_PREFIX."tags_entries SET entry_id=? WHERE entry_id=?";
        $params = array($new_entry_id, $old_entry_id);
        $query  = $this->executeQuery($sql, $params);
    }

    public function getEntriesByView($view, $user_id, $limit = '', $tag_id = 0)
    {
        switch ($view) {
            case 'archive':
                $sql    = "SELECT * FROM ".STORAGE_PREFIX."entries WHERE user_id=? AND is_read=? ";
                $params = array($user_id, 1);
                break;
            case 'fav' :
                $sql    = "SELECT * FROM ".STORAGE_PREFIX."entries WHERE user_id=? AND is_fav=? ";
                $params = array($user_id, 1);
                break;
            case 'tag' :
                $sql    = "SELECT ".STORAGE_PREFIX."entries.* FROM ".STORAGE_PREFIX."entries
                LEFT JOIN ".STORAGE_PREFIX."tags_entries ON ".STORAGE_PREFIX."tags_entries.entry_id=entries.id
                WHERE ".STORAGE_PREFIX."entries.user_id=? AND ".STORAGE_PREFIX."tags_entries.tag_id = ? ";
                $params = array($user_id, $tag_id);
                break;
            default:
                $sql    = "SELECT * FROM ".STORAGE_PREFIX."entries WHERE user_id=? AND is_read=? ";
                $params = array($user_id, 0);
                break;
        }

                $sql .= $this->getEntriesOrder().' ' . $limit;

                $query = $this->executeQuery($sql, $params);
                $entries = $query->fetchAll();

                return $entries;
    }

    public function getEntriesByViewCount($view, $user_id, $tag_id = 0)
    {
        switch ($view) {
            case 'archive':
                    $sql    = "SELECT count(*) FROM ".STORAGE_PREFIX."entries WHERE user_id=? AND is_read=? ";
                $params = array($user_id, 1);
                break;
            case 'fav' :
                    $sql    = "SELECT count(*) FROM ".STORAGE_PREFIX."entries WHERE user_id=? AND is_fav=? ";
                $params = array($user_id, 1);
                break;
            case 'tag' :
                $sql    = "SELECT count(*) FROM ".STORAGE_PREFIX."entries
                    LEFT JOIN ".STORAGE_PREFIX."tags_entries ON ".STORAGE_PREFIX."tags_entries.entry_id=entries.id
                    WHERE ".STORAGE_PREFIX."entries.user_id=? AND ".STORAGE_PREFIX."tags_entries.tag_id = ? ";
                $params = array($user_id, $tag_id);
                break;
            default:
                $sql    = "SELECT count(*) FROM ".STORAGE_PREFIX."entries WHERE user_id=? AND is_read=? ";
                $params = array($user_id, 0);
                break;
        }

        $query = $this->executeQuery($sql, $params);
        list($count) = $query->fetch();

        return $count;
    }

    public function updateContent($id, $content, $user_id)
    {
        $sql_action = 'UPDATE '.STORAGE_PREFIX.'entries SET content = ? WHERE id=? AND user_id=?';
        $params_action = array($content, $id, $user_id);
        $query = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    /**
     *
     * @param string $url
     * @param string $title
     * @param string $content
     * @param integer $user_id
     * @return integer $id of inserted record
     */
    public function add($url, $title, $content, $user_id, $isFavorite=0, $isRead=0)
    {
        $sql_action = 'INSERT INTO '.STORAGE_PREFIX.'entries ( url, title, content, user_id, is_fav, is_read ) VALUES (?, ?, ?, ?, ?, ?)';
        $params_action = array($url, $title, $content, $user_id, $isFavorite, $isRead);

        if ( !$this->executeQuery($sql_action, $params_action) ) {
          $id = null;
        }
        else {
          $id = intval($this->getLastId( (STORAGE == 'postgres') ? 'entries_id_seq' : '') );
        }
        return $id;
    }

    public function deleteById($id, $user_id)
    {
        $sql_action     = "DELETE FROM ".STORAGE_PREFIX."entries WHERE id=? AND user_id=?";
        $params_action  = array($id, $user_id);
        $query          = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    public function favoriteById($id, $user_id)
    {
        $sql_action     = "UPDATE ".STORAGE_PREFIX."entries SET is_fav=NOT is_fav WHERE id=? AND user_id=?";
        $params_action  = array($id, $user_id);
        $query          = $this->executeQuery($sql_action, $params_action);
    }

    public function archiveById($id, $user_id)
    {
        $sql_action     = "UPDATE ".STORAGE_PREFIX."entries SET is_read=NOT is_read WHERE id=? AND user_id=?";
        $params_action  = array($id, $user_id);
        $query          = $this->executeQuery($sql_action, $params_action);
    }

    public function archiveAll($user_id)
    {
        $sql_action     = "UPDATE ".STORAGE_PREFIX."entries SET is_read=? WHERE user_id=? AND is_read=?";
        $params_action  = array($user_id, 1, 0);
        $query          = $this->executeQuery($sql_action, $params_action);
    }

    public function getLastId($column = '')
    {
        return $this->getHandle()->lastInsertId($column);
    }

    public function search($term, $user_id, $limit = '')
    {
        $search = '%'.$term.'%';
        $sql_action = "SELECT * FROM ".STORAGE_PREFIX."entries WHERE user_id=? AND (content LIKE ? OR title LIKE ? OR url LIKE ?) "; //searches in content, title and URL
        $sql_action .= $this->getEntriesOrder().' ' . $limit;
        $params_action = array($user_id, $search, $search, $search);
        $query = $this->executeQuery($sql_action, $params_action);
        return $query->fetchAll();
  	}

    public function retrieveAllTags($user_id, $term = NULL)
    {
        $sql = "SELECT DISTINCT ".STORAGE_PREFIX."tags.*, count(".STORAGE_PREFIX."entries.id) AS entriescount FROM ".STORAGE_PREFIX."tags
          LEFT JOIN ".STORAGE_PREFIX."tags_entries ON ".STORAGE_PREFIX."tags_entries.tag_id=".STORAGE_PREFIX."tags.id
          LEFT JOIN ".STORAGE_PREFIX."entries ON ".STORAGE_PREFIX."tags_entries.entry_id=".STORAGE_PREFIX."entries.id
          WHERE ".STORAGE_PREFIX."entries.user_id=?
            ". (($term) ? "AND lower(".STORAGE_PREFIX."tags.value) LIKE ?" : '') ."
          GROUP BY ".STORAGE_PREFIX."tags.id, ".STORAGE_PREFIX."tags.value
          ORDER BY ".STORAGE_PREFIX."tags.value";
        $query = $this->executeQuery($sql, (($term)? array($user_id, strtolower('%'.$term.'%')) : array($user_id) ));
        $tags = $query->fetchAll();

        return $tags;
    }

    public function retrieveTag($id, $user_id)
    {
        $tag  = NULL;
        $sql    = "SELECT DISTINCT ".STORAGE_PREFIX."tags.* FROM ".STORAGE_PREFIX."tags
          LEFT JOIN ".STORAGE_PREFIX."tags_entries ON ".STORAGE_PREFIX."tags_entries.tag_id=".STORAGE_PREFIX."tags.id
          LEFT JOIN ".STORAGE_PREFIX."entries ON ".STORAGE_PREFIX."tags_entries.entry_id=".STORAGE_PREFIX."entries.id
          WHERE ".STORAGE_PREFIX."tags.id=? AND ".STORAGE_PREFIX."entries.user_id=?";
        $params = array(intval($id), $user_id);
        $query  = $this->executeQuery($sql, $params);
        $tag  = $query->fetchAll();

        return isset($tag[0]) ? $tag[0] : NULL;
    }

    public function retrieveEntriesByTag($tag_id, $user_id)
    {
        $sql =
            "SELECT ".STORAGE_PREFIX."entries.* FROM ".STORAGE_PREFIX."entries
            LEFT JOIN ".STORAGE_PREFIX."tags_entries ON ".STORAGE_PREFIX."tags_entries.entry_id=".STORAGE_PREFIX."entries.id
            WHERE ".STORAGE_PREFIX."tags_entries.tag_id = ? AND ".STORAGE_PREFIX."entries.user_id=? ORDER by ".STORAGE_PREFIX."entries.id DESC";
        $query = $this->executeQuery($sql, array($tag_id, $user_id));
        $entries = $query->fetchAll();

        return $entries;
    }

    public function retrieveTagsByEntry($entry_id)
    {
        $sql =
            "SELECT ".STORAGE_PREFIX."tags.* FROM ".STORAGE_PREFIX."tags
            LEFT JOIN ".STORAGE_PREFIX."tags_entries ON ".STORAGE_PREFIX."tags_entries.tag_id=".STORAGE_PREFIX."tags.id
            WHERE ".STORAGE_PREFIX."tags_entries.entry_id = ?";
        $query = $this->executeQuery($sql, array($entry_id));
        $tags = $query->fetchAll();

        return $tags;
    }

    public function removeTagForEntry($entry_id, $tag_id)
    {
        $sql_action     = "DELETE FROM ".STORAGE_PREFIX."tags_entries WHERE tag_id=? AND entry_id=?";
        $params_action  = array($tag_id, $entry_id);
        $query          = $this->executeQuery($sql_action, $params_action);
        return $query;
    }
    
    public function cleanUnusedTag($tag_id)
    {
        $sql_action = "SELECT ".STORAGE_PREFIX."tags.* FROM ".STORAGE_PREFIX."tags JOIN ".STORAGE_PREFIX."tags_entries ON ".STORAGE_PREFIX."tags_entries.tag_id=tags.id WHERE ".STORAGE_PREFIX."tags.id=?";
        $query = $this->executeQuery($sql_action,array($tag_id));
        $tagstokeep = $query->fetchAll();
        $sql_action = "SELECT ".STORAGE_PREFIX."tags.* FROM ".STORAGE_PREFIX."tags LEFT JOIN ".STORAGE_PREFIX."tags_entries ON ".STORAGE_PREFIX."tags_entries.tag_id=tags.id WHERE ".STORAGE_PREFIX."tags.id=?";
        $query = $this->executeQuery($sql_action,array($tag_id));
        $alltags = $query->fetchAll();
        
        foreach ($alltags as $tag) {
            if ($tag && !in_array($tag,$tagstokeep)) {
                $sql_action = "DELETE FROM ".STORAGE_PREFIX."tags WHERE id=?";
                $params_action = array($tag[0]);
                $this->executeQuery($sql_action, $params_action);
                return true;
            }
        }
        
    }

    public function retrieveTagByValue($value)
    {
        $tag  = NULL;
        $sql    = "SELECT * FROM ".STORAGE_PREFIX."tags WHERE value=?";
        $params = array($value);
        $query  = $this->executeQuery($sql, $params);
        $tag  = $query->fetchAll();

        return isset($tag[0]) ? $tag[0] : null;
    }

    public function createTag($value)
    {
        $sql_action = 'INSERT INTO '.STORAGE_PREFIX.'tags ( value ) VALUES (?)';
        $params_action = array($value);
        $query = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    public function setTagToEntry($tag_id, $entry_id)
    {
        $sql_action = 'INSERT INTO '.STORAGE_PREFIX.'tags_entries ( tag_id, entry_id ) VALUES (?, ?)';
        $params_action = array($tag_id, $entry_id);
        $query = $this->executeQuery($sql_action, $params_action);
        return $query;
    }

    private function getEntriesOrder()
    {
        if (isset($_SESSION['sort']) and array_key_exists($_SESSION['sort'], $this->order)) {
            return $this->order[$_SESSION['sort']];
        }
        else {
            return $this->order['default'];
        }
    }
}
