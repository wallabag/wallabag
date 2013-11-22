<?php

//Default Website
$app->mount('/', include 'front.php');

//Rest API
$app->mount('/api', include 'api.php');
