<?php

namespace Wallabag\Tests\Unit\Event\Subscriber;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Wallabag\Event\Subscriber\DisabledImporterSubscriber;
use Wallabag\Import\ImportChain;
use Wallabag\Import\ImportInterface;

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
        $pinboard = $this->createMock(ImportInterface::class);
        $pinboard->method('getUrl')->willReturn('import_pinboard');

        $importChain = new ImportChain(['pinboard']);
        $importChain->addImport($pinboard, 'pinboard');
        $importChain->addImport($this->createMock(ImportInterface::class), 'delicious');

        return new DisabledImporterSubscriber($importChain);
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
