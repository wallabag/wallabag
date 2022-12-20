<?php

namespace App\Tests\Import;

use App\Import\ImportChain;
use App\Import\ImportInterface;
use PHPUnit\Framework\TestCase;

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
