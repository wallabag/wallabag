<?php

namespace Tests\AnnotationBundle\Controller;

use Tests\Wallabag\AnnotationBundle\WallabagAnnotationTestCase;
use Wallabag\AnnotationBundle\Entity\Annotation;
use Wallabag\CoreBundle\Entity\Entry;

class AnnotationControllerTest extends WallabagAnnotationTestCase
{
    /**
     * Test fetching annotations for an entry
     */
    public function testGetAnnotations()
    {
        /** @var Annotation $annotation */
        $annotation = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findOneByUsername('admin');

        if (!$annotation) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->logInAs('admin');
        $this->client->request('GET', 'annotations/'.$annotation->getEntry()->getId().'.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, $content['total']);
        $this->assertEquals($annotation->getText(), $content['rows'][0]['text']);
    }

    /**
     * Test creating an annotation for an entry
     */
    public function testSetAnnotation()
    {
        $this->logInAs('admin');

        /** @var Entry $entry */
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
        $this->client->request('POST', 'annotations/'.$entry->getId().'.json', [], [], $headers, $content);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Big boss', $content['user']);
        $this->assertEquals('v1.0', $content['annotator_schema_version']);
        $this->assertEquals('my annotation', $content['text']);
        $this->assertEquals('my quote', $content['quote']);

        /** @var Annotation $annotation */
        $annotation = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findLastAnnotationByPageId($entry->getId(), 1);

        $this->assertEquals('my annotation', $annotation->getText());
    }

    /**
     * Test editing an existing annotation
     */
    public function testEditAnnotation()
    {
        /** @var Annotation $annotation */
        $annotation = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findOneByUsername('admin');

        $this->logInAs('admin');

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'a modified annotation',
        ]);
        $this->client->request('PUT', 'annotations/'.$annotation->getId().'.json', [], [], $headers, $content);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Big boss', $content['user']);
        $this->assertEquals('v1.0', $content['annotator_schema_version']);
        $this->assertEquals('a modified annotation', $content['text']);
        $this->assertEquals('my quote', $content['quote']);

        /** @var Annotation $annotationUpdated */
        $annotationUpdated = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findOneById($annotation->getId());
        $this->assertEquals('a modified annotation', $annotationUpdated->getText());
    }

    /**
     * Test deleting an annotation
     */
    public function testDeleteAnnotation()
    {
        /** @var Annotation $annotation */
        $annotation = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findOneByUsername('admin');

        $this->logInAs('admin');

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'a modified annotation',
        ]);
        $this->client->request('DELETE', 'annotations/'.$annotation->getId().'.json', [], [], $headers, $content);
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
