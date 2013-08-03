<?php

$env = new Twig_Environment();
$env->addFunction(new Twig_SimpleFunction('anonymous', function () {}));

return $env;
