<?php

putenv('APP_ENV=dev');
putenv('APP_DEBUG=1');

return require __DIR__ . '/../public/index.php';
