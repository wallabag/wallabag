<?php

namespace Wallabag\Tests\Unit\Import;

use PHPUnit\Framework\TestCase;
use Wallabag\Import\ImportChain;
use Wallabag\Import\ImportInterface;

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
