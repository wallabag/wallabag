<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Entity\Notification;

class NotificationControllerTest extends WallabagCoreTestCase
{
    public function testDisplayNotification()
    {
        $this->logInAs('admin');

        $client = $this->getClient();

        $em = $client->getContainer()
            ->get('doctrine.orm.entity_manager');

        $notification = new Notification($this->getLoggedInUser());
        $notification->setType(Notification::TYPE_USER)
            ->setTitle('fooTitle')
            ->setDescription('barDescription');

        $em->persist($notification);
        $em->flush();

        $crawler = $client->request('GET', '/');
        $this->assertCount(1, $notificationArea = $crawler->filter('.notifications-area .collection'));
        $this->assertContains('fooTitle', $notificationArea->text());
        $this->assertContains('barDescription', $notificationArea->text());
    }
}
