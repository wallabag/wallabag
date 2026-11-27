<?php

namespace Wallabag\Import;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Wallabag\Consumer\RabbitMQConsumerTotalProxy;
use Wallabag\Controller\Import\ImportController;

class ImportCompilerPass implements CompilerPassInterface
{
    private const INTERNAL_QUEUES = ['feeds'];

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ImportChain::class)) {
            return;
        }

        $chain = $container->getDefinition(ImportChain::class);
        $disabled = $this->getDisabledAliases($container);
        $disabledImporters = [];
        $queues = [];

        foreach ($container->findTaggedServiceIds('wallabag.import') as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $alias = $attributes['alias'];
                $queues[] = $alias;

                if (\in_array($alias, $disabled, true)) {
                    $disabledImporters[] = $alias;

                    continue;
                }

                $chain->addMethodCall('addImport', [new Reference($id), $alias]);
            }
        }

        $queues = [...$queues, ...self::INTERNAL_QUEUES];

        $container->setParameter('wallabag.import.disabled_importers', $disabledImporters);

        if ($container->hasDefinition(ImportController::class)) {
            $container->getDefinition(ImportController::class)->setArgument('$importQueues', $queues);
        }

        if ($container->hasDefinition(RabbitMQConsumerTotalProxy::class)) {
            $consumers = [];

            foreach ($queues as $queue) {
                if ($container->hasDefinition('old_sound_rabbit_mq.import_' . $queue . '_consumer')) {
                    $consumers[$queue] = new Reference('old_sound_rabbit_mq.import_' . $queue . '_consumer');
                }
            }

            $container->getDefinition(RabbitMQConsumerTotalProxy::class)->setArgument('$consumers', ServiceLocatorTagPass::register($container, $consumers));
        }
    }

    /**
     * @return string[]
     */
    private function getDisabledAliases(ContainerBuilder $container): array
    {
        $parameters = $container->getParameterBag()->all();
        $value = $container->resolveEnvPlaceholders($parameters['wallabag.disabled_importers'] ?? [], true);

        return array_values(array_filter(
            array_map(static fn ($alias) => trim((string) $alias), (array) $value),
            static fn (string $alias) => '' !== $alias,
        ));
    }
}
