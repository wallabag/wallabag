<?php

namespace Tests\Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Annotation;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;

class AnnotationControllerTest extends WallabagTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logInAs('admin');

        $this->client = $this->getTestClient();
    }

    /**
     * This data provider allow to tests annotation from the :
     *     - API POV (when user use the api to manage annotations)
     *     - and User POV (when user use the web interface - using javascript - to manage annotations).
     */
    public function dataForEachAnnotations()
    {
        return [
            ['/api/annotations'],
            ['annotations'],
        ];
    }

    /**
     * @dataProvider dataForEachAnnotations
     */
    public function testGetAnnotations($prefixUrl)
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);

        $user = $em
            ->getRepository(User::class)
            ->findOneByUserName('admin');
        $entry = $em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/entry1', $user->getId());

        if ('annotations' === $prefixUrl) {
            $this->logInAs('admin');
        }

        $this->client->request('GET', $prefixUrl . '/' . $entry->getId() . '.json');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertGreaterThanOrEqual(1, $content['total']);
    }

    /**
     * @dataProvider dataForEachAnnotations
     */
    public function testGetAnnotationsFromAnOtherUser($prefixUrl)
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $otherUser = $em
            ->getRepository(User::class)
            ->findOneByUserName('bob');
        $entry = $em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/entry3', $otherUser->getId());

        if ('annotations' === $prefixUrl) {
            $this->logInAs('admin');
        }

        $this->client->request('GET', $prefixUrl . '/' . $entry->getId() . '.json');
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider dataForEachAnnotations
     */
    public function testSetAnnotation($prefixUrl)
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);

        $user = $em
            ->getRepository(User::class)
            ->findOneByUserName('admin');

        if ('annotations' === $prefixUrl) {
            $this->logInAs('admin');
        }

        /** @var Entry $entry */
        $entry = $em
            ->getRepository(Entry::class)
            ->findOneByUsernameAndNotArchived('admin');

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'my annotation',
            'quote' => 'my quote',
            'ranges' => [
                ['start' => '', 'startOffset' => 24, 'end' => '', 'endOffset' => 31],
            ],
        ]);
        $this->client->request('POST', $prefixUrl . '/' . $entry->getId() . '.json', [], [], $headers, $content);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('Big boss', $content['user']);
        $this->assertSame('v1.0', $content['annotator_schema_version']);
        $this->assertSame('my annotation', $content['text']);
        $this->assertSame('my quote', $content['quote']);

        /** @var Annotation $annotation */
        $annotation = $em
            ->getRepository(Annotation::class)
            ->findLastAnnotationByUserId($entry->getId(), $user->getId());

        $this->assertSame('my annotation', $annotation->getText());
    }

    public function testAllowEmptyQuote()
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);

        /** @var Entry $entry */
        $entry = $em
            ->getRepository(Entry::class)
            ->findOneByUsernameAndNotArchived('admin');

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'my annotation',
            'quote' => null,
            'ranges' => [
                ['start' => '', 'startOffset' => 24, 'end' => '', 'endOffset' => 31],
            ],
        ]);
        $this->client->request('POST', '/api/annotations/' . $entry->getId() . '.json', [], [], $headers, $content);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('Big boss', $content['user']);
        $this->assertSame('v1.0', $content['annotator_schema_version']);
        $this->assertSame('my annotation', $content['text']);
        $this->assertSame('', $content['quote']);
    }

    public function testAllowOmmittedQuote()
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);

        /** @var Entry $entry */
        $entry = $em
            ->getRepository(Entry::class)
            ->findOneByUsernameAndNotArchived('admin');

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'my new annotation',
            'ranges' => [
                ['start' => '', 'startOffset' => 25, 'end' => '', 'endOffset' => 32],
            ],
        ]);
        $this->client->request('POST', '/api/annotations/' . $entry->getId() . '.json', [], [], $headers, $content);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('Big boss', $content['user']);
        $this->assertSame('v1.0', $content['annotator_schema_version']);
        $this->assertSame('my new annotation', $content['text']);
        $this->assertSame('', $content['quote']);
    }

    /**
     * @dataProvider dataForEachAnnotations
     */
    public function testSetAnnotationWithQuoteTooLong($prefixUrl)
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);

        if ('annotations' === $prefixUrl) {
            $this->logInAs('admin');
        }

        /** @var Entry $entry */
        $entry = $em
            ->getRepository(Entry::class)
            ->findOneByUsernameAndNotArchived('admin');

        $longQuote = str_repeat('a', 10001);
        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'my annotation',
            'quote' => $longQuote,
            'ranges' => [
                ['start' => '', 'startOffset' => 24, 'end' => '', 'endOffset' => 31],
            ],
        ]);
        $this->client->request('POST', $prefixUrl . '/' . $entry->getId() . '.json', [], [], $headers, $content);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider dataForEachAnnotations
     */
    public function testEditAnnotation($prefixUrl)
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);

        $user = $em
            ->getRepository(User::class)
            ->findOneByUserName('admin');
        $entry = $em
            ->getRepository(Entry::class)
            ->findOneByUsernameAndNotArchived('admin');

        $annotation = new Annotation($user);
        $annotation->setEntry($entry);
        $annotation->setText('This is my annotation /o/');
        $annotation->setQuote('my quote');

        $em->persist($annotation);
        $em->flush();

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'a modified annotation',
        ]);
        $this->client->request('PUT', $prefixUrl . '/' . $annotation->getId() . '.json', [], [], $headers, $content);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('Big boss', $content['user']);
        $this->assertSame('v1.0', $content['annotator_schema_version']);
        $this->assertSame('a modified annotation', $content['text']);
        $this->assertSame('my quote', $content['quote']);

        /** @var Annotation $annotationUpdated */
        $annotationUpdated = $em
            ->getRepository(Annotation::class)
            ->findOneById($annotation->getId());
        $this->assertSame('a modified annotation', $annotationUpdated->getText());

        $em->remove($annotationUpdated);
        $em->flush();
    }

    /**
     * @dataProvider dataForEachAnnotations
     */
    public function testEditAnnotationFromAnOtherUser($prefixUrl)
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $otherUser = $em
            ->getRepository(User::class)
            ->findOneByUserName('bob');
        $entry = $em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/entry3', $otherUser->getId());
        $annotation = $em
            ->getRepository(Annotation::class)
            ->findLastAnnotationByUserId($entry->getId(), $otherUser->getId());

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'a modified annotation',
        ]);
        $this->client->request('PUT', $prefixUrl . '/' . $annotation->getId() . '.json', [], [], $headers, $content);
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider dataForEachAnnotations
     */
    public function testDeleteAnnotation($prefixUrl)
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);

        $user = $em
            ->getRepository(User::class)
            ->findOneByUserName('admin');
        $entry = $em
            ->getRepository(Entry::class)
            ->findOneByUsernameAndNotArchived('admin');

        $annotation = new Annotation($user);
        $annotation->setEntry($entry);
        $annotation->setText('This is my annotation /o/');
        $annotation->setQuote('my quote');

        $em->persist($annotation);
        $em->flush();

        if ('annotations' === $prefixUrl) {
            $this->logInAs('admin');
        }

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'a modified annotation',
        ]);
        $this->client->request('DELETE', $prefixUrl . '/' . $annotation->getId() . '.json', [], [], $headers, $content);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('This is my annotation /o/', $content['text']);

        $annotationDeleted = $em
            ->getRepository(Annotation::class)
            ->findOneById($annotation->getId());

        $this->assertNull($annotationDeleted);
    }

    /**
     * @dataProvider dataForEachAnnotations
     */
    public function testDeleteAnnotationFromAnOtherUser($prefixUrl)
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $otherUser = $em
            ->getRepository(User::class)
            ->findOneByUserName('bob');
        $entry = $em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/entry3', $otherUser->getId());
        $annotation = $em
            ->getRepository(Annotation::class)
            ->findLastAnnotationByUserId($entry->getId(), $otherUser->getId());

        $user = $em
            ->getRepository(User::class)
            ->findOneByUserName('admin');
        $entry = $em
            ->getRepository(Entry::class)
            ->findOneByUsernameAndNotArchived('admin');

        if ('annotations' === $prefixUrl) {
            $this->logInAs('admin');
        }

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $content = json_encode([
            'text' => 'a modified annotation',
        ]);
        $this->client->request('DELETE', $prefixUrl . '/' . $annotation->getId() . '.json', [], [], $headers, $content);
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }
}
