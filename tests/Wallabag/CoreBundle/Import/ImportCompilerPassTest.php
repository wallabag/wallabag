<?php

namespace Tests\Wallabag\CoreBundle\Import;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wallabag\CoreBundle\Import\ImportChain;
use Wallabag\CoreBundle\Import\ImportCompilerPass;

class ImportCompilerPassTest extends TestCase
{
    public function testProcessNoDefinition()
    {
        $container = new ContainerBuilder();
        $res = $this->process($container);

        $this->assertNull($res);
    }

    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container
            ->register(ImportChain::class)
            ->setPublic(false)
        ;

        $container
            ->register('foo')
            ->addTag('wallabag_import.import', ['alias' => 'pocket'])
        ;

        $this->process($container);

        $this->assertTrue($container->hasDefinition(ImportChain::class));

        $definition = $container->getDefinition(ImportChain::class);
        $this->assertTrue($definition->hasMethodCall('addImport'));

        $calls = $definition->getMethodCalls();
        $this->assertSame('pocket', $calls[0][1][1]);
    }

    protected function process(ContainerBuilder $container)
    {
        $repeatedPass = new ImportCompilerPass();
        $repeatedPass->process($container);
    }
}
