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
    Model\get_config_value('username') => Model\get_config_value('api_token')
));

// Add a new link
$server->register('item.add', function ($url, $user_id, $fetch_it = true) {

    return Model\add_link($url, $user_id, $fetch_it);
});

// Get all bookmark items
$server->register('item.bookmark.list', function ($user_id, $offset = null, $limit = null) {

    return Model\get_bookmarks($user_id, $offset, $limit);
});

// Count bookmarks
$server->register('item.bookmark.count', function ($user_id) {

    return Model\count_bookmarks($user_id);
});

// Add a bookmark
$server->register('item.bookmark.create', function ($item_id, $user_id) {

    return Model\set_bookmark_value($item_id, 1, $user_id);
});

// Remove a bookmark
$server->register('item.bookmark.delete', function ($item_id, $user_id) {

    return Model\set_bookmark_value($item_id, 0, $user_id);
});

// Get all unread items
$server->register('item.list_unread', function ($user_id, $offset = null, $limit = null) {

    return Model\get_items('unread', $user_id, $offset, $limit);
});

// Count all unread items
$server->register('item.count_unread', function ($user_id) {

    return Model\count_entries('unread', $user_id);
});

// Get all read items
$server->register('item.list_read', function ($user_id, $offset = null, $limit = null) {

    return Model\get_items('read', $user_id, $offset, $limit);
});

// Count all read items
$server->register('item.count_read', function ($user_id) {

    return Model\count_entries('read', $user_id);
});

// Get one item
$server->register('item.info', function ($item_id, $user_id) {

    return Model\get_item($item_id, $user_id);
});

// Delete an item
$server->register('item.delete', function($item_id, $user_id) {

    return Model\set_item_removed($item_id, $user_id);
});

// Mark item as read
$server->register('item.mark_as_read', function($item_id, $user_id) {

    return Model\set_item_read($item_id, $user_id);
});

// Mark item as unread
$server->register('item.mark_as_unread', function($item_id, $user_id) {

    return Model\set_item_unread($item_id, $user_id);
});

// Change the status of list of items
$server->register('item.set_list_status', function($status, array $items, $user_id) {

    return Model\set_items_status($status, $items, $user_id);
});

// Mark all unread items as read
$server->register('item.mark_all_as_read', function($user_id) {

    return Model\mark_as_read($user_id);
});

// Get one tag
$server->register('tag.info', function($tag_id) {

    return Model\get_tag($tag_id);
});

// Remove a tag to an item
$server->register('tag.remove', function ($tag_id, $item_id) {

    return Model\remove_tag($tag_id, $item_id);
});

// Count tags
$server->register('tag.count', function () {

    return count(Model\get_tags());
});

// Get all tags
$server->register('tag.list', function () {

    return Model\get_tags();
});

// Get all items with this tag
$server->register('tag.item.list', function ($tag_id, $user_id) {

    return Model\get_entries_by_tag($tag_id, $user_id);
});

// Get all tags of an item
$server->register('item.tag.list', function ($item_id) {

    return Model\get_tags_by_item($item_id);
});

echo $server->execute();