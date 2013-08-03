<?php

$env = new Twig_Environment();
$env->addTest(new Twig_SimpleTest('anonymous', function () {}));

return $env;
