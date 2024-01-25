<?php

namespace Tests\Wallabag\CoreBundle\Import;

use PHPUnit\Framework\TestCase;
use Wallabag\CoreBundle\Import\ImportChain;
use Wallabag\CoreBundle\Import\ImportInterface;

class ImportChainTest extends TestCase
{
    public function testGetAll()
    {
        $import = $this->getMockBuilder(ImportInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $importChain = new ImportChain();
        $importChain->addImport($import, 'alias');

        $this->assertCount(1, $importChain->getAll());
        $this->assertSame($import, $importChain->getAll()['alias']);
    }
}
