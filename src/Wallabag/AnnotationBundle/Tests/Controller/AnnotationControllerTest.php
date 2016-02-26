<?php

namespace Wallabag\AnnotationBundle\Tests\Controller;

use Wallabag\AnnotationBundle\Tests\WallabagAnnotationTestCase;

class AnnotationControllerTest extends WallabagAnnotationTestCase
{
    public function testGetAnnotations()
    {
        $annotation = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findOneBy(array('user' => 1));

        if (!$annotation) {
            $this->markTestSkipped('No content found in db.');
        }
        $this->logInAs('admin');
        $crawler = $this->client->request('GET', 'annotations/'.$annotation->getEntry()->getId().'.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, $content['total']);
        $this->assertEquals($annotation->getText(), $content['rows'][0]['text']);
    }

    public function testSetAnnotation()
    {
        $this->logInAs('admin');

        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(array('user' => 1));

        $headers = array('CONTENT_TYPE' => 'application/json');
        $content = json_encode(array(
            'text' => 'my annotation',
            'quote' => 'my quote',
            'range' => '[{"start":"","startOffset":24,"end":"","endOffset":31}]',
            ));
        $crawler = $this->client->request('POST', 'annotations/'.$entry->getId().'.json', array(), array(), $headers, $content);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $annotation = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findLastAnnotationByPageId($entry->getId(), 1);

        $this->assertEquals('my annotation', $annotation->getText());
    }

    public function testEditAnnotation()
    {
        $annotation = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findOneBy(array('user' => 1));

        $this->logInAs('admin');

        $headers = array('CONTENT_TYPE' => 'application/json');
        $content = json_encode(array(
            'text' => 'a modified annotation',
            ));
        $crawler = $this->client->request('PUT', 'annotations/'.$annotation->getId().'.json', array(), array(), $headers, $content);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('a modified annotation', $content['text']);

        $annotationUpdated = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findAnnotationById($annotation->getId());
        $this->assertEquals('a modified annotation', $annotationUpdated->getText());
    }
}
