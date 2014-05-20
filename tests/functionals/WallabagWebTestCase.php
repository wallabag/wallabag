<?php
namespace Wallabag\Tests\Functionals;

use Wallabag\Tests\Functionals\Fixtures;

use Silex\WebTestCase;

use Wallabag\Schema;

class WallabagWebTestCase extends WebTestCase
{
    protected $app;

    public function createApplication()
    {
        require __DIR__.'/../../app/app.php';
        require __DIR__ . '/../../app/controllers/controllers.php';

        $app['db.options'] = array(
            'driver'   => 'pdo_sqlite',
            'path'     => __DIR__.'/wallabag_test.db'
        );

        $app['debug'] = true;
        $app['session.test'] = true;

        Schema::dropTables($app['db']);
        Schema::createTables($app['db']);

        $this->app = $app;

        return $app;
    }
}
