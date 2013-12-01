<?php
use Knp\Provider\ConsoleServiceProvider;
use Poche\Api\EntryApi;
use Poche\Repository\EntryRepository;

use Silex\Provider\FormServiceProvider;

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app->before(function () use ($app) {
    $app['twig']->addGlobal('layout', $app['twig']->loadTemplate('layout.twig'));
});

$app->register(new ConsoleServiceProvider(), [
    'console.name' => 'Poche console',
    'console.version' => '0.1',
    'console.project_directory' => __DIR__.'/..',
]);

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/../poche.db',
    ),
));

$app->register(new FormServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
));

$app['entry_repository'] = $app->share(function ($app) {
    return new EntryRepository($app['db']);
});

$app['entry_api'] = $app->share(function ($app) {
    return new EntryApi($app['entry_repository']);
});
