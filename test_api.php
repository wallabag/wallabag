<?php

require 'vendor/JsonRPC/Client.php';

use JsonRPC\Client;

$client = new Client('http://localhost/poche/2/jsonrpc.php');
$client->authentication('admin', 'PYBRAYc4ebUuRoa');

# fetch all bookmarks
// $result = $client->execute('item.bookmark.list');

# count bookmarks
// $result = $client->execute('item.bookmark.count');

# add a bookmark
// $result = $client->execute('item.bookmark.create', array(1));

# delete a bookmark
// $result = $client->execute('item.bookmark.delete', array(1));

# get all unread items
// $result = $client->execute('item.list_unread');

# count all unread items
// $result = $client->execute('item.count_unread');

# get all read items
// $result = $client->execute('item.list_read');

# count all read items
// $result = $client->execute('item.count_read');

# get one item
// $result = $client->execute('item.info', array(1));

# delete one item
// $result = $client->execute('item.delete', array(1));

# mark item as read
// $result = $client->execute('item.mark_as_read', array(1));

# mark item as unread
// $result = $client->execute('item.mark_as_unread', array(1));

# change the status of list of items
// $result = $client->execute('item.set_list_status', array('unread', array(1, 2, 3)));

# mark all unread items as read
// $result = $client->execute('item.mark_all_as_read');

# get one tag
// $result = $client->execute('tag.info', array(1));

# remove a tag to an entry
// $result = $client->execute('tag.remove', array(16, 3));

# count tags
// $result = $client->execute('tag.count');

# tags list
// $result = $client->execute('tag.list');

# list of items with this tag
// $result = $client->execute('tag.item.list', array(2));

# list of tags for an item
// $result = $client->execute('item.tag.list', array(3));

print_r($result);