<?php

$env = new Twig_Environment();
$env->addFilter(new Twig_SimpleFilter('anonymous', function () {}));

return $env;
