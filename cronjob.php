<?php

require 'common.php';

if (php_sapi_name() === 'cli') {

    $options = getopt('', array(
        'limit::'
    ));
}
else {

    $options = $_GET;
}

$limit = ! empty($options['limit']) && ctype_digit($options['limit']) ? (int) $options['limit'] : 10;

$user_id = 1;

$items = Model\unfetched_items($user_id, $limit);
foreach ($items as $item) {
    Model\fetch_content($item['id'], $user_id);    
}

Model\write_debug();