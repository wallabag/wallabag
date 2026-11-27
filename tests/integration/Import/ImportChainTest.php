<?php

namespace Wallabag\Tests\Integration\Import;

use Wallabag\Import\ImportChain;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class ImportChainTest extends WallabagKernelTestCase
{
    public function testRoutesFollowTheAliases(): void
    {
        $imports = static::getContainer()->get(ImportChain::class)->getAll();

        $this->assertNotEmpty($imports);

        foreach ($imports as $alias => $import) {
            $this->assertSame('import_' . $alias, $import->getUrl());
        }
    }
}
