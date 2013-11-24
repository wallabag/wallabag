<?php
namespace Poche\Tests\Functionals;

class ApiTest extends PocheWebTestCase
{
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
