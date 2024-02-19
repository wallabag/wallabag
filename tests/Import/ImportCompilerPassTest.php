<?php

namespace Tests\Wallabag\Import;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wallabag\Import\ImportChain;
use Wallabag\Import\ImportCompilerPass;

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
            ->addTag('wallabag.import', ['alias' => 'pocket'])
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
