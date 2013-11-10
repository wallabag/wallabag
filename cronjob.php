<?php

require 'common.php';

if (php_sapi_name() === 'cli') {

    $options = getopt('', array(
        'limit::',
        'user-id::',
    ));
}
else {

    $options = $_GET;
}

$limit = ! empty($options['limit']) && ctype_digit($options['limit']) ? (int) $options['limit'] : 10;
$user_id = ! empty($options['user-id']) && ctype_digit($options['user-id']) ? (int) $options['user-id'] : null;

if (is_null($user_id)) {
    die('You must give a user id');
}

$items = Model\unfetched_items($user_id, $limit);
foreach ($items as $item) {
    if ($item_to_update = Model\fetch_content($item['id'], $user_id)) {
        Model\update_item($item_to_update);
    }
}

Model\write_debug();