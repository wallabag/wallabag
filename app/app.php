<?php
use Knp\Provider\ConsoleServiceProvider;
use Wallabag\Api\EntryApi;
use Wallabag\Api\ContentFullTextRssApi;
use Wallabag\Repository\EntryRepository;
use Wallabag\Twig;

use Symfony\Component\Translation\Loader\PoFileLoader;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SecurityServiceProvider;

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

//Generate url in templates
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->before(function () use ($app) {
    $app['twig']->addGlobal('layout', $app['twig']->loadTemplate('layout.twig'));
});

$app['twig'] = $app->share($app->extend('twig', function($twig) {
  $twig->addFilter(new Twig_SimpleFilter('getDomain', 'Wallabag\Twig\Filter::getDomain'));
  return $twig;
}));

$app['twig'] = $app->share($app->extend('twig', function($twig) {
  $twig->addFilter(new Twig_SimpleFilter('getReadingTime', 'Wallabag\Twig\Filter::getReadingTime'));
  return $twig;
}));

$app['twig'] = $app->share($app->extend('twig', function($twig) {
  $twig->addFilter(new Twig_SimpleFilter('getPicture', 'Wallabag\Twig\Filter::getPicture'));
  return $twig;
}));

$app->register(new ConsoleServiceProvider(), [
    'console.name' => 'Wallabag console',
    'console.version' => '0.1',
    'console.project_directory' => __DIR__.'/..',
]);

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/../wallabag.db',
    ),
));

$app->register(new Silex\Provider\FormServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallbacks' => array('en'),
));

$app->register(new SessionServiceProvider());

$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'help' => array('pattern' => '^/help'),
        'default' => array(
            'pattern' => '^.*$',
            'anonymous' => true, 
            'form' => array('login_path' => '/', 'check_path' => 'login'),
            'logout' => array('logout_path' => '/logout'), 
            'users' => $app->share(function() use ($app) {
                return new Wallabag\User\UserProvider($app['db']);
            }),
        ),
    ),
    'security.access_rules' => array(
        array('^/.+$', 'ROLE_USER'),
        array('^/help$', ''), 
    )
));

$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('po', new PoFileLoader());

    $translator->addResource('po', __DIR__.'/locales/fr_FR.utf8/LC_MESSAGES/fr_FR.utf8.po', 'fr');
    $translator->addResource('po', __DIR__.'/locales/en_EN.utf8/LC_MESSAGES/en_EN.utf8.po', 'en');

    return $translator;
}));

$app['entry_repository'] = $app->share(function ($app) {
    return new EntryRepository($app['db']);
});

$app['content_api'] = $app->share(function ($app) {
    return new ContentFullTextRssApi();
});

$app['entry_api'] = $app->share(function ($app) {
    return new EntryApi($app['entry_repository'], $app['content_api']);
});


