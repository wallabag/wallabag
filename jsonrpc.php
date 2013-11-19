<?php
/**
 * poche API
 *
 * @package poche
 * @subpackage api
 * @license    http://www.gnu.org/licenses/agpl-3.0.html  GNU Affero GPL
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 */

require 'common.php';
require 'vendor/JsonRPC/Server.php';

use JsonRPC\Server;

$server = new Server;
$server->authentication(array(
    API_USER => Model\get_config_value('api_token')
));

function can_call_api($user_params)
{
    $user = Model\check_api_authentication($user_params);

    if ($user['id'] == '')
        return false;

    return $user;
}

// Add a new link
$server->register('item.add', function ($user_params, $url, $fetch_it = true) {

    if (! ($user = can_call_api($user_params)))
        return false;

    return Model\add_link($url, $user['id'], $fetch_it);
});

// Get all bookmark items
$server->register('item.bookmark.list', function ($user_params, $offset, $limit, $sort) {

    if (! ($user = can_call_api($user_params)))
        return false;

    return Model\get_bookmarks($user['id'], $offset, $limit, $sort);
});

// Count bookmarks
$server->register('item.bookmark.count', function ($user_params) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\count_bookmarks($user['id']);
});

// Add a bookmark
$server->register('item.bookmark.create', function ($user_params, $item_id) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\set_bookmark_value($item_id, 1, $user['id']);
});

// Remove a bookmark
$server->register('item.bookmark.delete', function ($user_params, $item_id) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\set_bookmark_value($item_id, 0, $user['id']);
});

// Get all unread items
$server->register('item.list_unread', function ($user_params, $offset, $limit) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\get_items('unread', $user['id'], $offset, $limit);
});

// Count all unread items
$server->register('item.count_unread', function ($user_params) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\count_entries('unread', $user['id']);
});

// Get all read items
$server->register('item.list_read', function ($user_params, $offset, $limit) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\get_items('read', $user['id'], $offset, $limit);
});

// Count all read items
$server->register('item.count_read', function ($user_params) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\count_entries('read', $user['id']);
});

// Get one item
$server->register('item.info', function ($user_params, $item_id) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\get_item($item_id, $user['id']);
});

// Delete an item
$server->register('item.delete', function($user_params, $item_id) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\set_item_removed($item_id, $user['id']);
});

// Mark item as read
$server->register('item.mark_as_read', function($user_params, $item_id) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\set_item_read($item_id, $user['id']);
});

// Mark item as unread
$server->register('item.mark_as_unread', function($user_params, $item_id) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\set_item_unread($item_id, $user['id']);
});

// Change the status of list of items
$server->register('item.set_list_status', function($user_params, $status, array $items) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\set_items_status($status, $items, $user['id']);
});

// Mark all unread items as read
$server->register('item.mark_all_as_read', function($user_params) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\mark_as_read($user['id']);
});

// Get one tag
$server->register('tag.info', function($user_params, $tag_id) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\get_tag($tag_id);
});

// Remove a tag to an item
$server->register('tag.remove', function ($user_params, $tag_id, $item_id) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\remove_tag($tag_id, $item_id);
});

// Count tags
$server->register('tag.count', function ($user_params) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return count(Model\get_tags());
});

// Get all tags
$server->register('tag.list', function ($user_params) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\get_tags();
});

// Get all items with this tag
$server->register('tag.item.list', function ($user_params, $tag_id) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\get_entries_by_tag($tag_id, $user['id']);
});

// Get all tags of an item
$server->register('item.tag.list', function ($user_params, $item_id) {

    if (! ($user = can_call_api($user_params)))
        return false;
    
    return Model\get_tags_by_item($item_id);
});

echo $server->execute();