<?php

namespace Wallabag\Tests\Unit\Event\Subscriber;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Wallabag\Event\Subscriber\DisabledImporterSubscriber;

class DisabledImporterSubscriberTest extends TestCase
{
    public function testDisabledImporterRouteIsNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->createSubscriber()->onKernelController($this->createEvent('import_pinboard'));
    }

    public function testEnabledImporterRouteIsAllowed(): void
    {
        $this->createSubscriber()->onKernelController($this->createEvent('import_delicious'));

        $this->addToAssertionCount(1);
    }

    public function testSubRequestIsIgnored(): void
    {
        $this->createSubscriber()->onKernelController($this->createEvent('import_pinboard', HttpKernelInterface::SUB_REQUEST));

        $this->addToAssertionCount(1);
    }

    private function createSubscriber(): DisabledImporterSubscriber
    {
        return new DisabledImporterSubscriber([' pinboard']);
    }

    private function createEvent(string $route, int $type = HttpKernelInterface::MAIN_REQUEST): ControllerEvent
    {
        return new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            static fn () => null,
            new Request([], [], ['_route' => $route]),
            $type,
        );
    }
}
