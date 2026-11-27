<?php

namespace Wallabag\Tests\Unit\Import;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wallabag\Controller\Import\ImportController;
use Wallabag\Import\ImportChain;
use Wallabag\Import\ImportCompilerPass;

class ImportCompilerPassTest extends TestCase
{
    public function testProcessNoDefinition(): void
    {
        $container = new ContainerBuilder();
        $this->process($container);

        $this->assertFalse($container->hasDefinition(ImportChain::class));
    }

    public function testProcess(): void
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

    public function testProcessKeepsDisabledImportersOutOfTheRegistry(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('wallabag.disabled_importers', ['pinboard', ' ', 'unknown']);
        $container->register(ImportChain::class)->setPublic(false);
        $container->register(ImportController::class)->setPublic(false);

        foreach (['pinboard', 'readability'] as $alias) {
            $container->register('import.' . $alias)->addTag('wallabag.import', ['alias' => $alias]);
        }

        $this->process($container);

        $calls = $container->getDefinition(ImportChain::class)->getMethodCalls();
        $this->assertCount(1, $calls);
        $this->assertSame('readability', $calls[0][1][1]);

        $this->assertTrue($container->hasDefinition('import.pinboard'));
        $this->assertSame(['pinboard'], $container->getParameter('wallabag.import.disabled_importers'));
        $this->assertSame(['pinboard', 'readability', 'feeds'], $container->getDefinition(ImportController::class)->getArgument('$importQueues'));
    }

    protected function process(ContainerBuilder $container): void
    {
        $repeatedPass = new ImportCompilerPass();
        $repeatedPass->process($container);
    }
}
