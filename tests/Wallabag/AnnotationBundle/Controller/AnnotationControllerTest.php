<?php

namespace Tests\AnnotationBundle\Controller;

use Tests\Wallabag\AnnotationBundle\WallabagAnnotationTestCase;

class AnnotationControllerTest extends WallabagAnnotationTestCase
{
    public function testGetAnnotations()
    {
        $annotation = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findOneByUsername('admin');

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
            ->findOneByUsernameAndNotArchived('admin');

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'my annotation',
            'quote' => 'my quote',
            'ranges' => ['start' => '', 'startOffset' => 24, 'end' => '', 'endOffset' => 31],
        ]);
        $crawler = $this->client->request('POST', 'annotations/'.$entry->getId().'.json', [], [], $headers, $content);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Big boss', $content['user']);
        $this->assertEquals('v1.0', $content['annotator_schema_version']);
        $this->assertEquals('my annotation', $content['text']);
        $this->assertEquals('my quote', $content['quote']);

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
            ->findOneByUsername('admin');

        $this->logInAs('admin');

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'a modified annotation',
        ]);
        $crawler = $this->client->request('PUT', 'annotations/'.$annotation->getId().'.json', [], [], $headers, $content);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Big boss', $content['user']);
        $this->assertEquals('v1.0', $content['annotator_schema_version']);
        $this->assertEquals('a modified annotation', $content['text']);
        $this->assertEquals('my quote', $content['quote']);

        $annotationUpdated = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findOneById($annotation->getId());
        $this->assertEquals('a modified annotation', $annotationUpdated->getText());
    }

    public function testDeleteAnnotation()
    {
        $annotation = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findOneByUsername('admin');

        $this->logInAs('admin');

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'a modified annotation',
        ]);
        $crawler = $this->client->request('DELETE', 'annotations/'.$annotation->getId().'.json', [], [], $headers, $content);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('a modified annotation', $content['text']);

        $annotationDeleted = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findOneById($annotation->getId());

        $this->assertNull($annotationDeleted);
    }
}
