<?php


$api = $app['controllers_factory'];
$api->get('/', function () { return 'API home page'; });

$api->get('/entries', function () use ($app) {
    $entries = $app['entry_api']->getEntries();
    return json_encode($entries);
});

return $api;
