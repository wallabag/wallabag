<?php
/**
 * Model calls
 *
 * All the calls to the models
 * @package poche
 * @subpackage model
 * @license    http://www.gnu.org/licenses/agpl-3.0.html  GNU Affero GPL
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 */
namespace Model;

require_once 'vendor/PicoFeed/Filter.php';
require_once 'vendor/PicoFeed/Export.php';
require_once 'vendor/PicoFeed/Import.php';
require_once 'vendor/PicoFeed/Reader.php';
require_once 'vendor/SimpleValidator/Validator.php';
require_once 'vendor/SimpleValidator/Base.php';
require_once 'vendor/SimpleValidator/Validators/Required.php';
require_once 'vendor/SimpleValidator/Validators/Unique.php';
require_once 'vendor/SimpleValidator/Validators/MaxLength.php';
require_once 'vendor/SimpleValidator/Validators/MinLength.php';
require_once 'vendor/SimpleValidator/Validators/Integer.php';
require_once 'vendor/SimpleValidator/Validators/Equals.php';
require_once 'vendor/SimpleValidator/Validators/Integer.php';

use SimpleValidator\Validator;
use SimpleValidator\Validators;
use PicoFeed\Import;
use PicoFeed\Reader;
use PicoFeed\Export;

/**
 * database version
 */
const DB_VERSION = 1;

/**
 * user agent used to fetch content
 */
const HTTP_USERAGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.62 Safari/537.36';


/**
 * Return an array of sorting directions
 * 
 * @return array list of sorting descriptions
 */
function get_sorting_directions()
{
    return array(
        'asc' => t('Older items first'),
        'desc' => t('Most recent first'),
    );
}

/**
 * Return an array with available languages
 * 
 * @return array list of available languages
 */
function get_languages()
{
    $languages = array(
        'cs_CZ' => t('Czech'),
        'de_DE' => t('German'),
        'en_US' => t('English'),
        'es_ES' => t('Spanish'),
        'fr_FR' => t('French'),
        'it_IT' => t('Italian'),
        'pt_BR' => t('Portuguese'),
        'zh_CN' => t('Simplified Chinese'),
    );

    asort($languages);

    return $languages;
}


/**
 * Return an array with available themes
 * 
 * @return array list of available themes
 */
function get_themes()
{
    $themes = array(
        'original' => t('Original')
    );

    if (file_exists(THEME_DIRECTORY)) {

        $dir = new \DirectoryIterator(THEME_DIRECTORY);

        foreach ($dir as $fileinfo) {

            if (! $fileinfo->isDot() && $fileinfo->isDir()) {
                $themes[$dir->getFilename()] = ucfirst($dir->getFilename());
            }
        }
    }

    return $themes;
}


/**
 * Return an array of paging options
 * 
 * @return array list of paging options
 */
function get_paging_options()
{
    return array(
        5 => 5,
        10 => 10,
        25 => 25,
        50 => 50,
        100 => 100,
        150 => 150,
        200 => 200,
        250 => 250,
    );
}


/**
 * If DEBUG is enabled, logs are written in DEBUG_FILENAME file
 */
function write_debug()
{
    if (DEBUG) {

        $data = '';

        foreach (\PicoFeed\Logging::$messages as $line) {
            $data .= $line.PHP_EOL;
        }

        file_put_contents(DEBUG_FILENAME, $data);
    }
}


/**
 * Return the id of current user
 * 
 * @return int id of current user
 */
function get_user_id()
{
    return $_SESSION['user']['id'];
}


/**
 * Generate a new token
 * 
 * @return string a token generated with /dev/urandom or mt_rand()
 */
function generate_token()
{
    if (ini_get('open_basedir') === '') {
        return substr(base64_encode(file_get_contents('/dev/urandom', false, null, 0, 20)), 0, 15);
    }
    else {
        return substr(base64_encode(uniqid(mt_rand(), true)), 0, 20);
    }
}


/**
 * Update user profile to store new tokens
 *
 * @param  int $user_id id of the user
 * @return boolean
 */
function new_tokens($user_id)
{
    $values = array(
        'api_token' => generate_token(),
        'feed_token' => generate_token(),
    );

    foreach ($values as $key => $value) {
        $_SESSION['user'][$key] = $value;
    }

    return \PicoTools\singleton('db')->table('users')->eq('id', $user_id)->update($values);
}


/**
 * Save the third authentication 
 * 
 * @param  string $type  name of the service (eg: google, mozilla)
 * @param  string $value the new token
 * @param  int $user_id id of the user
 * @return boolean
 */
function save_auth_token($type, $value, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('users')
        ->eq('id', $user_id)
        ->update(array(
            'auth_'.$type.'_token' => $value
        ));
}


/**
 * Remove the third authentication
 * 
 * @param  string $type  name of the service (eg: google, mozilla)
 * @return string $value the new token
 */
function remove_auth_token($type)
{
    \PicoTools\singleton('db')
        ->table('users')
        ->eq('username', $_SESSION['user']['username'])
        ->update(array(
            'auth_'.$type.'_token' => ''
        ));

    $_SESSION['user']['auth_'.$type.'_token'] = '';
}


/**
 * Search a query in the poched links
 * 
 * @param  string $query   the query to search
 * @param  int $user_id id of the user
 * @return array list of poched links which contain $query term
 */
function search_items($query, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->columns(
            'id',
            'title',
            'url',
            'status',
            'content',
            'updated',
            'bookmark'
        )
        ->eq('user_id', $user_id)
        ->beginOr()
        ->like('title', '%'.$query.'%')
        ->like('content', '%'.$query.'%')
        ->closeOr()
        ->findAll();
}


/**
 * Update an item 
 * 
 * @param  array $item New values of the item (the id is necessary)
 * @return boolean
 */
function update_item($item) 
{
    return \PicoTools\singleton('db')
    ->table('entries')
    ->eq('id', $item['id'])
    ->save($item);
}

/**
 * Save a new link for a given user
 * 
 * @param string  $url the URL to save
 * @param int  $user_id the user who wants to save a link
 * @param boolean $fetch_it if true, the content will be fetched
 * @return integer the id of the poched link
 */
function add_link($url, $user_id, $fetch_it = true)
{
    $title = $content = '';

    $db = \PicoTools\singleton('db');
    $db->startTransaction();

    if (! $db->table('entries')->save(array(
        'url' => $url,
        'updated' => time(),
        'status' => 'unread',
        'fetched' => 0,
        'user_id' => $user_id
    ))) {
        return false;
    }

    $id = $db->getConnection()->getLastId();

    $db->closeTransaction();
    write_debug();

    if ($fetch_it) {
        if ($item = fetch_content($id, $user_id)) {
            update_item($item);
        }

    }

    return $id;
}


/**
 * Parse the fetched content with Readability to remove useless components
 * 
 * @param  string $content content of the poched link
 * @param  string $url URL of the poched link
 * @return array array with title and content
 */
function parse_content_with_readability($content, $url)
{
    require_once 'vendor/Readability/Readability.php';

    if (! empty($content)) {

        $readability = new \Readability($content, $url);
        if ($readability->init()) {
            return array(
                'content' => $readability->getContent()->innerHTML,
                'title' => $readability->getTitle()->textContent
            );
        }
    }

    return array(
        'content' => 'Problem with fetching content',
        'title' => 'Untitled'
    );
}


/**
 * Return a list of items, filtered by $status
 * 
 * @param  string $status status of the returned items (unread, read)
 * @param  int $user_id id of the user 
 * @param  int $offset the offset where start the list 
 * @param  int $limit number of items to return
 * @param  string $order_column the column to sort
 * @param  string $order_direction the direction (asc or desc) to sort
 * @return array list of the items
 */
function get_items($status, $user_id, $offset = null, $limit = null, $order_column = 'updated', $order_direction = 'desc')
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->columns(
            'id',
            'title',
            'url',
            'status',
            'content',
            'updated',
            'bookmark'
        )
        ->eq('user_id', $user_id)
        ->eq('status', $status)
        ->eq('fetched', '1')
        ->orderBy($order_column, $order_direction)
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}


/**
 * Count the $status entries
 * 
 * @param  string $status status of the items (unread, read)
 * @param  int $user_id id of the user 
 * @return int number of $status entries
 */
function count_entries($status, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('user_id', $user_id)
        ->eq('status', $status)
        ->eq('fetched', '1')
        ->count();
}


/**
 * Count the bookmarked entries
 * 
 * @param  int $user_id id of the user 
 * @return int number of bookmarked entries
 */
function count_bookmarks($user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('user_id', $user_id)
        ->eq('bookmark', 1)
        ->eq('fetched', '1')
        ->in('status', array('read', 'unread'))
        ->count();
}


/**
 * Return a list of bookmarked items
 * 
 * @param  int $user_id id of the user 
 * @param  int $offset the offset where start the list 
 * @param  int $limit number of items to return
 * @param  string $items_sorting_direction sort by asc or desc
 * @return array list of the bookmarked items
 */
function get_bookmarks($user_id, $offset = null, $limit = null, $items_sorting_direction)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->columns(
            'id',
            'title',
            'updated',
            'url',
            'bookmark',
            'status',
            'content'
        )
        ->in('status', array('read', 'unread'))
        ->eq('user_id', $user_id)
        ->eq('bookmark', 1)
        ->eq('fetched', '1')
        ->orderBy('updated', $items_sorting_direction)
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}


/**
 * Get the informations of an item
 * 
 * @param  int $id id of the item
 * @param  int $user_id id of the user 
 * @return array array with the informations
 */
function get_item($id, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('id', $id)
        ->eq('user_id', $user_id)
        ->findOne();
}


/**
 * Remove a tag to an entry
 * 
 * @param  int $tag_id id of the tag
 * @param  int $entry_id id of the entry
 * @return boolean
 */
function remove_tag($tag_id, $entry_id)
{
    return \PicoTools\singleton('db')
        ->table('tags_entries')
        ->eq('tag_id', $tag_id)
        ->eq('entry_id', $entry_id)
        ->remove();
}


/**
 * Get the tags list
 *
 * @todo get tags of the current user
 * @return array list of tags
 */
function get_tags()
{
    return \PicoTools\singleton('db')
        ->table('tags')
        ->columns(
            'tags.id',
            'tags.value'
        )
        ->findAll();
}

/**
 * Get the items associated to a tag
 * 
 * @param  int $tag_id id of the tag
 * @param  int $user_id id of the user
 * @return array list of items which have the tag $tag_id
 */
function get_entries_by_tag($tag_id, $user_id) 
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->columns(
            'entries.id',
            'entries.title',
            'entries.updated',
            'entries.url',
            'entries.bookmark',
            'entries.status',
            'entries.content'
        )
        ->join('tags_entries', 'entry_id', 'id')
        ->eq('tag_id', $tag_id)
        ->eq('fetched', '1')
        ->eq('user_id', $user_id)
        ->findAll();
}


/**
 * Get information about a tag
 * 
 * @param  int $id id of the tag
 * @return array array with the information of the tag
 */
function get_tag($id)
{
    return \PicoTools\singleton('db')
        ->table('tags')
        ->eq('id', $id)
        ->findOne();
}

/**
 * Get all the tags of an item
 *
 * @param  int $item_id id of the item
 * @return array array of the tags
 */
function get_tags_by_item($item_id) 
{
    return \PicoTools\singleton('db')
        ->table('tags')
        ->columns(
            'tags.id',
            'tags.value'
        )
        ->join('tags_entries', 'tag_id', 'id')
        ->eq('entry_id', $item_id)
        ->findAll();
}


/**
 * Get an array to create a navigation system, around an item
 * 
 * @param  array $item the item
 * @param  array $user the user
 * @param  array  $status array of the items status
 * @param  array  $bookmark array with bookmark values
 * @return array list of the items near $item
 */
function get_nav_item($item, $user, $status = array('unread'), $bookmark = array(1, 0))
{
    $query = \PicoTools\singleton('db')
        ->table('entries')
        ->columns('id', 'status', 'title', 'bookmark')
        ->neq('status', 'removed')
        ->eq('fetched', '1')
        ->eq('user_id', $user['id'])
        ->orderBy('updated', $user['items_sorting_direction']);

    $items = $query->findAll();

    $next_item = null;
    $previous_item = null;

    for ($i = 0, $ilen = count($items); $i < $ilen; $i++) {

        if ($items[$i]['id'] == $item['id']) {

            if ($i > 0) {

                $j = $i - 1;

                while ($j >= 0) {

                    if (in_array($items[$j]['status'], $status) && in_array($items[$j]['bookmark'], $bookmark)) {
                        $previous_item = $items[$j];
                        break;
                    }

                    $j--;
                }
            }

            if ($i < ($ilen - 1)) {

                $j = $i + 1;

                while ($j < $ilen) {

                    if (in_array($items[$j]['status'], $status) && in_array($items[$j]['bookmark'], $bookmark)) {
                        $next_item = $items[$j];
                        break;
                    }

                    $j++;
                }
            }

            break;
        }
    }

    return array(
        'next' => $next_item,
        'previous' => $previous_item
    );
}


/**
 * Remove an item 
 * 
 * @param int $id id of the item
 * @param int $user_id id of the user
 * @return boolean
 */
function set_item_removed($id, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('id', $id)
        ->eq('user_id', $user_id)
        ->save(array('status' => 'removed', 'content' => ''));
}


/**
 * Mark an item as read
 * 
 * @param int $id id of the item
 * @param int $user_id id of the user
 * @return boolean
 */
function set_item_read($id, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('id', $id)
        ->eq('user_id', $user_id)
        ->save(array('status' => 'read'));
}


/**
 * Mark an item as unread
 * 
 * @param int $id id of the item
 * @param int $user_id id of the user
 * @return boolean
 */
function set_item_unread($id, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('id', $id)
        ->eq('user_id', $user_id)
        ->save(array('status' => 'unread'));
}


/**
 * Mark a list of items as $status
 *
 * @param string $status the $items will have this status
 * @param array $items array of items to update
 * @param int $user_id id of the user
 * @return boolean
 */
function set_items_status($status, array $items, $user_id)
{
    if (! in_array($status, array('read', 'unread', 'removed'))) return false;

    return \PicoTools\singleton('db')
        ->table('entries')
        ->in('id', $items)
        ->eq('user_id', $user_id)
        ->save(array('status' => $status));
}


/**
 * Un/bookmark an item
 * 
 * @param int $id id of the item
 * @param int $value 0 if unbookmark, 1 if bookmark
 * @param int $user_id id of the user
 * @return boolean
 */
function set_bookmark_value($id, $value, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('id', $id)
        ->eq('user_id', $user_id)
        ->save(array('bookmark' => $value));
}


/**
 * Change the status of item
 * 
 * @param  int $id id of the item
 * @param  id $user_id id of the user
 * @return string the new status of the item
 */
function switch_item_status($id, $user_id)
{
    $item = \PicoTools\singleton('db')
        ->table('entries')
        ->columns('status')
        ->eq('id', $id)
        ->eq('user_id', $user_id)
        ->findOne();

    if ($item['status'] == 'unread') {

        \PicoTools\singleton('db')
            ->table('entries')
            ->eq('id', $id)
            ->save(array('status' => 'read'));

        return 'read';
    }
    else {

        \PicoTools\singleton('db')
            ->table('entries')
            ->eq('id', $id)
            ->save(array('status' => 'unread'));

        return 'unread';
    }

    return '';
}


/**
 * Mark all items of a user as read
 * 
 * @param  int $user_id id of the user
 * @return boolean
 */
function mark_as_read($user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('status', 'unread')
        ->eq('user_id', $user_id)
        ->save(array('status' => 'read'));
}


/**
 * Mark the $items_id of $user_id as read
 * 
 * @param  array  $items_id [description]
 * @param  int $user_id id of the user
 */
function mark_items_as_read(array $items_id, $user_id)
{
    \PicoTools\singleton('db')->startTransaction();

    foreach ($items_id as $id) {
        set_item_read($id, $user_id);
    }

    \PicoTools\singleton('db')->closeTransaction();
}


/**
 * Remove all items of a user
 * 
 * @param  int $user_id id of the user
 * @return boolean
 */
function mark_as_removed($user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('status', 'read')
        ->eq('bookmark', 0)
        ->eq('user_id', $user_id)
        ->save(array('status' => 'removed', 'content' => ''));
}


/**
 * Get the list of unfetched items of a user
 * 
 * @param  int $user_id id of the user
 * @param  int $limit number of items to return
 * @return array array of unfetched items
 */
function unfetched_items($user_id, $limit)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->columns(
            'id',
            'title',
            'url',
            'status',
            'content',
            'updated',
            'bookmark'
        )
        ->eq('user_id', $user_id)
        ->eq('fetched', '0')
        ->offset(0)
        ->limit($limit)
        ->findAll();
}


/**
 * Fetch the content of the $item_id
 * 
 * @param  int $item_id id of the item
 * @param  int $user_id id of the user
 * @return array array with id, title, content, updated time and fetched boolean
 */
function fetch_content($item_id, $user_id)
{
    $item = get_item($item_id, $user_id);
    $url = $item['url'];

    $client = \PicoFeed\Client::create();
    $client->url = $url;
    $client->timeout = HTTP_TIMEOUT;
    $client->user_agent = HTTP_USERAGENT;
    $client->execute();

    $html = $client->getContent();

    if (!empty($html)) {
        $results = parse_content_with_readability($html, $url);
        $filter = new \PicoFeed\Filter($results['content'], $url);
        $content = $filter->execute();
        $title = $results['title'];

        return array(
            'id' => $item_id,
            'title' => $title,
            'updated' => time(),
            'content' => $content,
            'fetched' => 1,
        );
    }

    return false;
}


/**
 * Get a user with $value (only used for token)
 * 
 * @param  string $column column (auth_mozilla_token, auth_google_token)
 * @param  string $value value of the token
 * @return array a user
 */
function get_user_by_config($column, $value)
{
    return \PicoTools\singleton('db')
        ->table('users')
        ->eq($column, $value)
        ->findOne();
}


/**
 * Get a user by id
 * 
 * @param  int $id the id of the user
 * @return array a user
 */
function get_user_by_id($id)
{
    return \PicoTools\singleton('db')
        ->table('users')
        ->eq('id', $id)
        ->findOne();
}


/**
 * Get a user by username
 * 
 * @param  string $username the username of the user
 * @return array a user
 */
function get_user($username)
{
    return \PicoTools\singleton('db')
        ->table('users')
        ->eq('username', $username)
        ->findOne();
}


/**
 * Validate the filled field while login and create the user session
 * 
 * @param  array $values fields to validate
 * @return array the user if login is right and errors if it's not
 */
function validate_login(array $values)
{
    $v = new Validator($values, array(
        new Validators\Required('username', t('The user name is required')),
        new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
        new Validators\Required('password', t('The password is required'))
    ));

    $result = $v->execute();
    $errors = $v->getErrors();

    if ($result) {
        $user = get_user($values['username']);

        if ($user && \password_verify($values['password'], $user['password'])) {

            unset($user['password']);
            $_SESSION['user'] = $user;
        }
        else {

            $result = false;
            $errors['login'] = t('Bad username or password');
        }
    }

    return array(
        $result,
        $errors
    );
}


/**
 * Validate the field when a tag is added
 * 
 * @param  array $values fields to validate
 * @return array the tag if right, errors if not
 */
function validate_tags(array $values)
{
    $v = new Validator($values, array(
        new Validators\Required('value', t('The value is required')),
    ));

    return array(
        $v->execute(),
        $v->getErrors()
    );
}


/**
 * Save the tags in database
 * If the tags exists, we get its id and we don't recreate it
 * 
 * @param  array  $values values of the tag
 * @return boolean
 */
function save_tags(array $values)
{

    $tags_values = explode(' ', $values['value']);

    $db = \PicoTools\singleton('db');
    $db->startTransaction();

    foreach($tags_values as $key => $tag_value) {

        $tag = \PicoTools\singleton('db')
                    ->table('tags')
                    ->eq('value', $tag_value)
                    ->findOne();
        $tag_id = $tag['id'];

        // Checks if tag exists
        if (is_null($tag_id)) {

            # create the tag
            $tag = $db->table('tags')->save(array(
                'value' => $tag_value,
            ));

            $tag_id = $db->getConnection()->getLastId();
        }

        # assign the tag to the article
        $db->table('tags_entries')->save(array(
                'entry_id' => $values['entry_id'],
                'tag_id' => $tag_id
        ));
    }

    $db->closeTransaction();
    write_debug();

    return true;
}


/**
 * Validate the filled field while updating preferences
 * 
 * @param  array $values fields to validate
 * @return array the user if updating is right and errors if it's not
 */
function validate_config_update(array $values)
{
    if (! empty($values['password'])) {

        $v = new Validator($values, array(
            new Validators\Required('username', t('The user name is required')),
            new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
            new Validators\Required('password', t('The password is required')),
            new Validators\MinLength('password', t('The minimum length is 6 characters'), 6),
            new Validators\Required('confirmation', t('The confirmation is required')),
            new Validators\Equals('password', 'confirmation', t('Passwords doesn\'t match')),
            new Validators\Required('items_per_page', t('Value required')),
            new Validators\Integer('items_per_page', t('Must be an integer')),
            new Validators\Required('theme', t('Value required')),
        ));
    }
    else {

        $v = new Validator($values, array(
            new Validators\Required('username', t('The user name is required')),
            new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
            new Validators\Required('items_per_page', t('Value required')),
            new Validators\Integer('items_per_page', t('Must be an integer')),
            new Validators\Required('theme', t('Value required')),
        ));
    }

    return array(
        $v->execute(),
        $v->getErrors()
    );
}


/**
 * Save the preferences in database
 * 
 * @param  array  $values values of the user
 * @return boolean
 */
function save_config(array $values)
{
    if (! DEMO_MODE) {
        // Update the password if needed
        if (! empty($values['password'])) {
            $values['password'] = \password_hash($values['password'], PASSWORD_BCRYPT);
        } else {
            unset($values['password']);
        }

        unset($values['confirmation']);
    }
    
    unset($values['username']);
    // Reload configuration in session
    foreach ($values as $key => $value) {
        $_SESSION['user'][$key] = $value;
    }    

    // Reload translations for flash session message
    \PicoTools\Translator\load($values['language']);

    return \PicoTools\singleton('db')->table('users')->update($values);
}


/**
 * Add a value for a plugin option 
 * 
 * @param string $name name of the plugin 
 * @param string $value value of the option
 * @return boolean
 */
function add_plugin_option($name, $value)
{
    $db = \PicoTools\singleton('db');
    $db->startTransaction();

    if (! $db->table('plugin_options')->save(array(
        'name' => $name,
        'value' => $value
    ))) {

        return false;
    }

    $id = $db->getConnection()->getLastId();
    $db->closeTransaction();

    return true;
}


/**
 * Get a value for a plugin option
 * 
 * @param  string $name name of the plugin 
 * @return array the value
 */
function get_plugin_option($name)
{
    return \PicoTools\singleton('db')
        ->table('plugin_options')
        ->eq('name', $name)
        ->findOne();
}


/**
 * Remove an option for a plugin
 * 
 * @param  string $name name of the plugin 
 * @return boolean
 */
function remove_plugin_option($name)
{
    return \PicoTools\singleton('db')
        ->table('plugin_options')
        ->eq('name', $name)
        ->remove();
}
