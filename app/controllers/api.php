<?php

$api = $app['controllers_factory'];
$api->get('/', function () { return 'API home page'; });

return $api;
