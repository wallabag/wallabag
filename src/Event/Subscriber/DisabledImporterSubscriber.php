<?php

namespace Wallabag\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class DisabledImporterSubscriber implements EventSubscriberInterface
{
    /** @var string[] */
    private readonly array $disabledRoutes;

    /**
     * @param array<string|null> $disabledImporters
     */
    public function __construct(array $disabledImporters = [])
    {
        $this->disabledRoutes = array_map(static fn ($alias) => 'import_' . trim((string) $alias), $disabledImporters);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (\in_array($event->getRequest()->attributes->get('_route'), $this->disabledRoutes, true)) {
            throw new NotFoundHttpException('This importer is disabled.');
        }
    }
}
