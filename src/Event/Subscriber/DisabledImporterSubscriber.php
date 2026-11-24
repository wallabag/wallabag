<?php

namespace Wallabag\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Wallabag\Import\ImportChain;

class DisabledImporterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ImportChain $importChain,
    ) {
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

        if (\in_array($event->getRequest()->attributes->get('_route'), $this->importChain->getDisabledRoutes(), true)) {
            throw new NotFoundHttpException('This importer is disabled.');
        }
    }
}
