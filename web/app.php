<?php

putenv('APP_ENV=prod');
putenv('APP_DEBUG=0');

return require __DIR__ . '/../public/index.php';
