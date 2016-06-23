<?php

namespace Tests\Wallabag\ImportBundle\Import;

use Wallabag\ImportBundle\Import\ImportChain;

class ImportChainTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAll()
    {
        $import = $this->getMockBuilder('Wallabag\ImportBundle\Import\ImportInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $importChain = new ImportChain();
        $importChain->addImport($import, 'alias');

        $this->assertCount(1, $importChain->getAll());
        $this->assertEquals($import, $importChain->getAll()['alias']);
    }
}
