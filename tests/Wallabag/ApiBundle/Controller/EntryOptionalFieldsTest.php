<?php

namespace Tests\Wallabag\ApiBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;
use Tests\Wallabag\ApiBundle\WallabagApiTestCase;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Wallabag\UserBundle\Entity\User;

class EntryOptionalFieldsTest extends WallabagApiTestCase
{
    private $createdEntryIds = [];
    private $createdTagIds = [];

    protected function tearDown(): void
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        foreach ($this->createdEntryIds as $id) {
            $entry = $em->find(Entry::class, $id);
            if (null !== $entry) {
                $em->remove($entry);
            }
        }
        $em->flush();
        foreach ($this->createdTagIds as $id) {
            $tag = $em->find(Tag::class, $id);
            if (null !== $tag) {
                $em->remove($tag);
            }
        }
        $em->flush();
        parent::tearDown();
    }

    /**
     * @dataProvider provideClearedFields
     */
    public function testClearOptionalField($method, $field, $value)
    {
        $entry = $this->createPopulatedEntry();
        $expected = $this->snapshot($entry);
        $expected[$field] = null;
        if ('title' === $field) {
            $expected[$field] = 'article';
        } elseif ('content' === $field) {
            $expected['reading_time'] = 0;
        } elseif ('tags' === $field || 'authors' === $field) {
            $expected[$field] = [];
        } elseif ('archive' === $field) {
            $expected[$field] = false;
            $expected['archived_at'] = null;
        } elseif ('starred' === $field) {
            $expected[$field] = false;
            $expected['starred_at'] = null;
        }

        $this->mockExtraction($method);
        $this->requestEntry($method, $entry, [$field => $value]);
        $this->assertSame($expected, $this->reloadSnapshot($entry->getId()));

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $responseField = ['authors' => 'published_by', 'archive' => 'is_archived', 'starred' => 'is_starred', 'public' => 'uid'][$field] ?? $field;
        $expectedValue = $expected[$field];
        if ('archive' === $field || 'starred' === $field) {
            $expectedValue = 0;
        }
        $this->assertSame($expectedValue, $response[$responseField]);
    }

    public function provideClearedFields()
    {
        foreach (['PATCH', 'POST'] as $method) {
            foreach (['title', 'content', 'language', 'preview_picture', 'published_at', 'authors', 'tags', 'origin_url', 'archive', 'starred', 'public'] as $field) {
                foreach (['empty string' => '', 'null' => null] as $label => $value) {
                    yield $method . ' ' . $field . ' ' . $label => [$method, $field, $value];
                }
            }
            foreach (['tags', 'authors'] as $field) {
                yield $method . ' ' . $field . ' empty array' => [$method, $field, []];
            }
            foreach (['archive', 'starred', 'public'] as $field) {
                foreach ([0, '0', false] as $index => $value) {
                    yield $method . ' ' . $field . ' false value ' . $index => [$method, $field, $value];
                }
            }
        }
    }

    /**
     * @dataProvider provideMethods
     */
    public function testOmittedFieldsArePreserved($method)
    {
        $entry = $this->createPopulatedEntry();
        $expected = $this->snapshot($entry);
        if ('PATCH' === $method) {
            $expected['title'] = 'Updated title';
        }
        $this->mockExtraction($method);
        $this->requestEntry($method, $entry, 'PATCH' === $method ? ['title' => 'Updated title'] : []);
        $this->assertSame($expected, $this->reloadSnapshot($entry->getId()));
    }

    /**
     * @dataProvider provideMethods
     */
    public function testTagsAreAppendedOnPostAndReplacedOnPatch($method)
    {
        $entry = $this->createPopulatedEntry();
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $existingTag = $em->getRepository(Tag::class)->findOneByLabel('new tag');
        $this->mockExtraction($method);
        $this->requestEntry($method, $entry, ['tags' => 'new tag']);
        $this->assertSame('POST' === $method ? ['new tag', 'original tag'] : ['new tag'], $this->reloadSnapshot($entry->getId())['tags']);
        if (null === $existingTag) {
            $this->createdTagIds[] = $this->client->getContainer()->get(EntityManagerInterface::class)->getRepository(Tag::class)->findOneByLabel('new tag')->getId();
        }
    }

    public function testPatchKeepsZeroStringsAndValidatesMetadata()
    {
        $entry = $this->createPopulatedEntry();
        $expected = $this->snapshot($entry);
        $expected['title'] = '0';
        $expected['authors'] = ['0'];
        $expected['published_at'] = '1970-01-01T00:00:00+00:00';
        $this->mockExtraction('PATCH');
        $this->requestEntry('PATCH', $entry, [
            'title' => '0',
            'authors' => '0',
            'published_at' => 0,
            'language' => 'invalid-locale',
            'preview_picture' => 'not a URL',
        ]);
        $this->assertSame($expected, $this->reloadSnapshot($entry->getId()));
    }

    public function provideMethods()
    {
        yield 'PATCH' => ['PATCH'];
        yield 'POST' => ['POST'];
    }

    public function testPostClearsMetadataAfterFetchingANewEntry()
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $entry = new Entry($em->getReference(User::class, $this->getUserId()));
        $entry->setUrl('https://example.org/new-article?case=' . uniqid());
        $this->mockExtraction('POST', static function (Entry $fetchedEntry) {
            $fetchedEntry->setTitle('Fetched title');
            $fetchedEntry->setContent('Fetched content');
            $fetchedEntry->setReadingTime(5);
            $fetchedEntry->setLanguage('fr');
            $fetchedEntry->setPreviewPicture('https://example.org/fetched.jpg');
            $fetchedEntry->setPublishedAt(new \DateTime('2020-01-01'));
            $fetchedEntry->setPublishedBy(['Fetched author']);
        });
        $this->requestEntry('POST', $entry, [
            'title' => null,
            'content' => null,
            'language' => null,
            'preview_picture' => null,
            'published_at' => null,
            'authors' => null,
        ]);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $saved = $this->reloadSnapshot($response['id']);
        $this->assertSame('new-article', $saved['title']);
        $this->assertNull($saved['content']);
        $this->assertSame(0, $saved['reading_time']);
        $this->assertNull($saved['language']);
        $this->assertNull($saved['preview_picture']);
        $this->assertNull($saved['published_at']);
        $this->assertSame([], $saved['authors']);
    }

    public function testPatchContentKeepsOmittedMetadata()
    {
        $entry = $this->createPopulatedEntry();
        $this->client->getContainer()->set(\Wallabag\CoreBundle\Helper\RuleBasedTagger::class, $this->createMock(\Wallabag\CoreBundle\Helper\RuleBasedTagger::class));
        $expected = $this->snapshot($entry);
        $expected['content'] = 'Replacement content';
        $expected['reading_time'] = (int) \Wallabag\CoreBundle\Tools\Utils::getReadingTime('Replacement content');
        $this->requestEntry('PATCH', $entry, ['content' => 'Replacement content']);
        $this->assertSame($expected, $this->reloadSnapshot($entry->getId()));
    }

    private function createPopulatedEntry(): Entry
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $entry = new Entry($em->getReference(User::class, $this->getUserId()));
        $entry->setUrl('https://example.org/article?case=' . uniqid());
        $entry->setDomainName('example.org');
        $entry->setTitle('Original title');
        $entry->setContent('Original content');
        $entry->setReadingTime(7);
        $entry->setLanguage('fr');
        $entry->setPreviewPicture('https://example.org/image.jpg');
        $entry->setPublishedAt(new \DateTime('2020-01-02T03:04:05+00:00'));
        $entry->setPublishedBy(['Original author']);
        $entry->setOriginUrl('https://example.org/source');
        $entry->updateArchived(true);
        $entry->setArchivedAt(new \DateTime('2020-02-03T04:05:06+00:00'));
        $entry->updateStar(true);
        $entry->setStarredAt(new \DateTime('2020-03-04T05:06:07+00:00'));
        $entry->generateUid();
        $tag = $em->getRepository(Tag::class)->findOneByLabel('original tag');
        $newTag = null === $tag;
        if ($newTag) {
            $tag = (new Tag())->setLabel('original tag');
        }
        $entry->addTag($tag);
        $em->persist($entry);
        $em->flush();
        $this->createdEntryIds[] = $entry->getId();
        if ($newTag) {
            $this->createdTagIds[] = $tag->getId();
        }

        return $entry;
    }

    private function mockExtraction($method, $callback = null): void
    {
        /** @var Container $container */
        $container = $this->client->getContainer();
        $proxy = $this->getMockBuilder(ContentProxy::class)
            ->setConstructorArgs([
                $container->get(\Graby\Graby::class),
                $container->get(\Wallabag\CoreBundle\Helper\RuleBasedTagger::class),
                $container->get(\Wallabag\CoreBundle\Helper\RuleBasedIgnoreOriginProcessor::class),
                $container->get('validator'),
                new \Psr\Log\NullLogger(),
                $container->getParameter('wallabag_core.fetching_error_message'),
            ])
            ->setMethods(['updateEntry'])
            ->getMock();
        $expectation = $proxy->expects('POST' === $method ? $this->once() : $this->never())->method('updateEntry');
        if (null !== $callback) {
            $expectation->willReturnCallback($callback);
        }
        $container->set(ContentProxy::class, $proxy);
    }

    private function requestEntry($method, Entry $entry, array $data): void
    {
        if ('POST' === $method) {
            $data['url'] = $entry->getUrl();
        }
        $path = 'POST' === $method ? '/api/entries.json' : '/api/entries/' . $entry->getId() . '.json';
        $this->client->request($method, $path, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode((object) $data));
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        if (!\in_array($response['id'], $this->createdEntryIds, true)) {
            $this->createdEntryIds[] = $response['id'];
        }
    }

    private function reloadSnapshot($id): array
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        return $this->snapshot($em->getRepository(Entry::class)->find($id));
    }

    private function snapshot(Entry $entry): array
    {
        $tags = array_map(static function (Tag $tag) { return $tag->getLabel(); }, $entry->getTags()->toArray());
        sort($tags);

        return [
            'title' => $entry->getTitle(),
            'content' => $entry->getContent(),
            'reading_time' => $entry->getReadingTime(),
            'language' => $entry->getLanguage(),
            'preview_picture' => $entry->getPreviewPicture(),
            'published_at' => $entry->getPublishedAt() ? $entry->getPublishedAt()->format('c') : null,
            'authors' => $entry->getPublishedBy(),
            'tags' => $tags,
            'origin_url' => $entry->getOriginUrl(),
            'archive' => $entry->isArchived(),
            'archived_at' => $entry->getArchivedAt() ? $entry->getArchivedAt()->format('c') : null,
            'starred' => $entry->isStarred(),
            'starred_at' => $entry->getStarredAt() ? $entry->getStarredAt()->format('c') : null,
            'public' => $entry->getUid(),
        ];
    }
}
