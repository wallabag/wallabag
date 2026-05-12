<?php

require_once __DIR__.'/../vendor/autoload_runtime.php';

return function (array $context): AppKernel {
    return new AppKernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
