<?php
namespace Poche\Tests\Functionals;

use Poche\Tests\Functionals\Fixtures;

use Silex\WebTestCase;

use Poche\Schema;

class PocheWebTestCase extends WebTestCase
{
    protected $app;

    public function createApplication()
    {
        require __DIR__.'/../../app/app.php';
        require __DIR__ . '/../../app/controllers/controllers.php';

        $app['db.options'] = array(
            'driver'   => 'pdo_sqlite',
            'path'     => __DIR__.'/poche_test.db'
        );

        $app['debug'] = true;

        Schema::dropTables($app['db']);
        Schema::createTables($app['db']);

        $this->app = $app;

        return $app;
    }
}
