<?php
error_reporting(E_ALL);
include_once 'inc/poche/global.inc.php';
include_once 'inc/poche/config.inc.php';

if (php_sapi_name() === 'cli') {
    $options_cli = getopt('', array(
        'limit::',
        'user-id::',
        'token::',
    ));
}
else {
    $options_cli = $_GET;
}

$limit = ! empty($options_cli['limit']) && ctype_digit($options_cli['limit']) ? (int) $options_cli['limit'] : 10;
$user_id = ! empty($options_cli['user-id']) && ctype_digit($options_cli['user-id']) ? (int) $options_cli['user-id'] : null;
$token = ! empty($options_cli['token']) ? $options_cli['token'] : null;

if (is_null($user_id)) {
    die('You must give a user id');
}

if (is_null($token)) {
    die('You must give a token');
}

$store = new Database();
$config = $store->getConfigUser($user_id);

if ($token != $config['token']) {
    die(_('Uh, there is a problem with the cron.'));
}

$items = $store->retrieveUnfetchedEntries($user_id, $limit);

foreach ($items as $item) {
    $url = new Url(base64_encode($item['url']));
    $content = Tools::getPageContent($url);

    $title = ($content['rss']['channel']['item']['title'] != '') ? $content['rss']['channel']['item']['title'] : _('Untitled');
    $body = $content['rss']['channel']['item']['description'];

    // // clean content from prevent xss attack
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    $title = $purifier->purify($title);
    $body = $purifier->purify($body);


    $store->updateContentAndTitle($item['id'], $title, $body, $user_id);
}