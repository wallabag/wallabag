<?php

namespace Wallabag\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wallabag\Tests\Functional\WallabagTestCase;

class CaptchaControllerTest extends WallabagTestCase
{
    public function testUnknownSessionKeyIsRejectedAnonymously(): void
    {
        $client = $this->getTestClient();

        $client->request('GET', '/_captcha/generate-captcha/unknown');

        $this->assertSame(Response::HTTP_PRECONDITION_REQUIRED, $client->getResponse()->getStatusCode());
        $this->assertSame('image/jpeg', $client->getResponse()->headers->get('Content-Type'));
    }
}
