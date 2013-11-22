<?php
namespace Poche\Tests\Functionals;

use Silex\WebTestCase;

class ApiTest extends WebTestCase
{
    public function createApplication()
    {
        require __DIR__.'/../../app/app.php';
        require __DIR__ . '/../../app/controllers/controllers.php';

        return $app;
    }

    public function testGetEntries()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/api/entries');

        $this->assertTrue($client->getResponse()->isOk());


        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertTrue($client->getResponse()->getContent() == '[]');
    }
}
