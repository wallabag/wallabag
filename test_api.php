<?php
require 'vendor/JsonRPC/Client.php';

use JsonRPC\Client;

$client = new Client('http://localhost/poche/poche/jsonrpc.php');
$client->authentication('api_user', 'n/RM6gT34CQfT+F');

$user_params = array(
    'username' => 'poche',
    'api_token' => 'j9IWi/s2omxFvH5'
    );

# poche a link
$result = $client->execute('item.add', array($user_params, 'http://cdetc.fr', true));

# fetch all bookmarks
// $result = $client->execute('item.bookmark.list', array($user_params, null, null, 'asc'));

# count bookmarks
// $result = $client->execute('item.bookmark.count', array($user_params));

# add a bookmark
// $result = $client->execute('item.bookmark.create', array($user_params, 1));

# delete a bookmark
// $result = $client->execute('item.bookmark.delete', array($user_params, 1));

# get all unread items
// $result = $client->execute('item.list_unread', array($user_params, null, null));

# count all unread items
// $result = $client->execute('item.count_unread', array($user_params));

# get all read items
// $result = $client->execute('item.list_read', array($user_params, null, null));

# count all read items
// $result = $client->execute('item.count_read', array($user_params));

# get one item
// $result = $client->execute('item.info', array($user_params, 1));

# delete one item
// $result = $client->execute('item.delete', array($user_params, 1));

# mark item as read
// $result = $client->execute('item.mark_as_read', array($user_params, 1));

# mark item as unread
// $result = $client->execute('item.mark_as_unread', array($user_params, 1));

# change the status of list of items
// $result = $client->execute('item.set_list_status', array($user_params, 'unread', array(1, 2, 3)));

# mark all unread items as read
// $result = $client->execute('item.mark_all_as_read', array($user_params));

# get one tag
// $result = $client->execute('tag.info', array($user_params, 1));

# remove a tag to an entry
// $result = $client->execute('tag.remove', array($user_params, 16, 3));

# count tags
// $result = $client->execute('tag.count', array($user_params));

# tags list
// $result = $client->execute('tag.list', array($user_params));

# list of items with this tag
// $result = $client->execute('tag.item.list', array($user_params, 2));

# list of tags for an item
// $result = $client->execute('item.tag.list', array($user_params, 3));

print_r($result);