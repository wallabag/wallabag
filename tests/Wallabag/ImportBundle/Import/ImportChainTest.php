<?php

namespace Tests\Wallabag\ImportBundle\Import;

use PHPUnit\Framework\TestCase;
use Wallabag\ImportBundle\Import\ImportChain;

class ImportChainTest extends TestCase
{
    public function testGetAll()
    {
        $import = $this->getMockBuilder('Wallabag\ImportBundle\Import\ImportInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $importChain = new ImportChain();
        $importChain->addImport($import, 'alias');

        $this->assertCount(1, $importChain->getAll());
        $this->assertSame($import, $importChain->getAll()['alias']);
    }
}
