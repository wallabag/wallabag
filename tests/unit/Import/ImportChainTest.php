<?php

namespace Wallabag\Tests\Unit\Import;

use PHPUnit\Framework\TestCase;
use Wallabag\Import\ImportChain;
use Wallabag\Import\ImportInterface;

class ImportChainTest extends TestCase
{
    public function testGetAll(): void
    {
        $import = $this->getMockBuilder(ImportInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $importChain = new ImportChain();
        $importChain->addImport($import, 'alias');

        $this->assertCount(1, $importChain->getAll());
        $this->assertSame($import, $importChain->getAll()['alias']);
    }

    public function testDisabledImportsAreHidden(): void
    {
        $enabled = $this->createMock(ImportInterface::class);
        $disabled = $this->createMock(ImportInterface::class);
        $disabled->method('getUrl')->willReturn('import_disabled');

        $importChain = new ImportChain(['disabled', ' ', null]);
        $importChain->addImport($enabled, 'enabled');
        $importChain->addImport($disabled, 'disabled');

        $this->assertSame(['enabled' => $enabled], $importChain->getAll());
        $this->assertSame(['import_disabled'], $importChain->getDisabledRoutes());
    }
}
