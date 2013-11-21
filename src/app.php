<?php
use Knp\Provider\ConsoleServiceProvider;

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app->register(new ConsoleServiceProvider(), [
    'console.name' => '%normalized_name%',
    'console.version' => '0',
    'console.project_directory' => __DIR__.'/..',
]);
