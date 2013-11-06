<?php

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


const DB_VERSION     = 1;
const HTTP_USERAGENT = 'poche - http://inthepoche.com';
const HTTP_FAKE_USERAGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.62 Safari/537.36';


function get_sorting_directions()
{
    return array(
        'asc' => t('Older items first'),
        'desc' => t('Most recent first'),
    );
}


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


function generate_token()
{
    if (ini_get('open_basedir') === '') {
        return substr(base64_encode(file_get_contents('/dev/urandom', false, null, 0, 20)), 0, 15);
    }
    else {
        return substr(base64_encode(uniqid(mt_rand(), true)), 0, 20);
    }
}


function new_tokens()
{
    $values = array(
        'api_token' => generate_token(),
        'feed_token' => generate_token(),
    );

    foreach ($values as $key => $value) {
        $_SESSION['user'][$key] = $value;
    }

    return \PicoTools\singleton('db')->table('users')->eq('username', $_SESSION['user']['username'])->update($values);
}


function save_auth_token($type, $value)
{
    return \PicoTools\singleton('db')
        ->table('users')
        ->eq('username', $_SESSION['user']['username'])
        ->update(array(
            'auth_'.$type.'_token' => $value
        ));
}


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
        list($title, $content) = fetch_content($id, $user_id);
    }

    return $id;
}


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

    return '';
}


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


function count_entries($status, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('user_id', $user_id)
        ->eq('status', $status)
        ->eq('fetched', '1')
        ->count();
}


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


function get_bookmarks($user_id, $offset = null, $limit = null)
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
        ->orderBy('updated', get_config_value('items_sorting_direction'))
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}


function get_item($id, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('id', $id)
        ->eq('user_id', $user_id)
        ->findOne();
}


function remove_tag($tag_id, $entry_id)
{
    return \PicoTools\singleton('db')
        ->table('tags_entries')
        ->eq('tag_id', $tag_id)
        ->eq('entry_id', $entry_id)
        ->remove();
}


function get_tags()
{
    //TODO get tags of the current user
    return \PicoTools\singleton('db')
        ->table('tags')
        ->columns(
            'tags.id',
            'tags.value'
        )
        ->findAll();
}


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

function get_tag($id)
{
    return \PicoTools\singleton('db')
        ->table('tags')
        ->eq('id', $id)
        ->findOne();
}


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


function get_nav_item($item, $user_id, $status = array('unread'), $bookmark = array(1, 0))
{
    $query = \PicoTools\singleton('db')
        ->table('entries')
        ->columns('id', 'status', 'title', 'bookmark')
        ->neq('status', 'removed')
        ->eq('fetched', '1')
        ->eq('user_id', $user_id)
        ->orderBy('updated', get_config_value('items_sorting_direction'));

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


function set_item_removed($id, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('id', $id)
        ->eq('user_id', $user_id)
        ->save(array('status' => 'removed', 'content' => ''));
}


function set_item_read($id, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('id', $id)
        ->eq('user_id', $user_id)
        ->save(array('status' => 'read'));
}


function set_item_unread($id, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('id', $id)
        ->eq('user_id', $user_id)
        ->save(array('status' => 'unread'));
}


function set_items_status($status, array $items, $user_id)
{
    if (! in_array($status, array('read', 'unread', 'removed'))) return false;

    return \PicoTools\singleton('db')
        ->table('entries')
        ->in('id', $items)
        ->eq('user_id', $user_id)
        ->save(array('status' => $status));
}


function set_bookmark_value($id, $value, $user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('id', $id)
        ->eq('user_id', $user_id)
        ->save(array('bookmark' => $value));
}


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


// Mark all items as read
function mark_as_read($user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('status', 'unread')
        ->eq('user_id', $user_id)
        ->save(array('status' => 'read'));
}


// Mark only specified items as read
function mark_items_as_read(array $items_id, $user_id)
{
    \PicoTools\singleton('db')->startTransaction();

    foreach ($items_id as $id) {
        set_item_read($id, $user_id);
    }

    \PicoTools\singleton('db')->closeTransaction();
}


function mark_as_removed($user_id)
{
    return \PicoTools\singleton('db')
        ->table('entries')
        ->eq('status', 'read')
        ->eq('bookmark', 0)
        ->eq('user_id', $user_id)
        ->save(array('status' => 'removed', 'content' => ''));
}


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


function fetch_content($id, $user_id)
{
    $item = get_item($id, $user_id);
    $url = $item['url'];
    # Fetch the URL to store the content in database
    # Else, the crontab will execute mass import
    $client = \PicoFeed\Client::create();
    $client->url = $url;
    $client->timeout = HTTP_TIMEOUT;
    $client->user_agent = HTTP_FAKE_USERAGENT;
    $client->execute();

    $html = $client->getContent();

    if (! empty($html)) {
        $results = parse_content_with_readability($html, $url);

        // Filter content
        $filter = new \PicoFeed\Filter($results['content'], $url);
        $content = $filter->execute();
        $title = $results['title'];

        \PicoTools\singleton('db')
        ->table('entries')
        ->eq('id', $id)
        ->save(array(
            'title' => $title,
            'updated' => time(),
            'content' => $content,
            'fetched' => 1,
        ));

        return true;
    }

    return false;
}


function get_user_by_config($column, $value)
{
    return \PicoTools\singleton('db')
        ->table('users')
        ->eq($column, $value)
        ->findOne();
}


function get_config_value($name)
{
    if (! isset($_SESSION)) {

        return \PicoTools\singleton('db')->table('config')->findOneColumn($name);
    }
    else {
        if (! isset($_SESSION['config'])) {
            $_SESSION['config'] = get_config();
        }

        if (isset($_SESSION['config'][$name])) {
            return $_SESSION['config'][$name];
        }
    }
    return null;
}


function get_config()
{
    return \PicoTools\singleton('db')
        ->table('config')
        ->columns(
            'language',
            'items_per_page',
            'theme',
            'items_sorting_direction'
        )
        ->findOne();
}


function get_user($username)
{
    return \PicoTools\singleton('db')
        ->table('users')
        ->eq('username', $username)
        ->findOne();
}


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
            $_SESSION['config'] = get_config();
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


function save_config(array $values)
{
    // Update the password if needed
    if (! empty($values['password'])) {
        $values['password'] = \password_hash($values['password'], PASSWORD_BCRYPT);
    } else {
        unset($values['password']);
    }

    unset($values['confirmation']);

    // Reload configuration in session
    foreach ($values as $key => $value) {
        $_SESSION['user'][$key] = $value;
    }    

    // Reload translations for flash session message
    \PicoTools\Translator\load($values['language']);

    return \PicoTools\singleton('db')->table('users')->update($values);
}


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


function get_plugin_option($name)
{
    return \PicoTools\singleton('db')
        ->table('plugin_options')
        ->eq('name', $name)
        ->findOne();
}


function remove_plugin_option($name)
{
    return \PicoTools\singleton('db')
        ->table('plugin_options')
        ->eq('name', $name)
        ->remove();
}
