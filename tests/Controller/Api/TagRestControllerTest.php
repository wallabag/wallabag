<?php

namespace Tests\Wallabag\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\Tag;

class TagRestControllerTest extends WallabagApiTestCase
{
    private $otherUserTagLabel = 'bob';

    public function testGetUserTags()
    {
        $this->client->request('GET', '/api/tags.json');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content);
        $this->assertArrayHasKey('id', $content[0]);
        $this->assertArrayHasKey('label', $content[0]);
        $this->assertArrayHasKey('nbEntries', $content[0]);

        $tagLabels = array_map(fn ($i) => $i['label'], $content);

        $this->assertNotContains($this->otherUserTagLabel, $tagLabels, 'There is a possible tag leak');
    }

    public function testDeleteUserTag()
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneWithTags($this->user->getId());

        $entry = $entry[0];

        $tagLabel = 'tagtest';
        $tag = new Tag();
        $tag->setLabel($tagLabel);
        $em->persist($tag);

        $entry->addTag($tag);

        $em->persist($entry);
        $em->flush();

        $this->client = $this->createAuthorizedClient();

        $this->client->request('DELETE', '/api/tags/' . $tag->getId() . '.json');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('label', $content);
        $this->assertSame($tag->getLabel(), $content['label']);
        $this->assertSame($tag->getSlug(), $content['slug']);

        $entries = $em->getRepository(Entry::class)
            ->findAllByTagId($this->user->getId(), $tag->getId());

        $this->assertCount(0, $entries);

        $tag = $em->getRepository(Tag::class)->findOneByLabel($tagLabel);

        $this->assertNull($tag, $tagLabel . ' was removed because it begun an orphan tag');
    }

    public function testDeleteOtherUserTag()
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $tag = $em->getRepository(Tag::class)->findOneByLabel($this->otherUserTagLabel);

        $this->client->request('DELETE', '/api/tags/' . $tag->getId() . '.json');

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    public function dataForDeletingTagByLabel()
    {
        return [
            'by_query' => [true],
            'by_body' => [false],
        ];
    }

    /**
     * @dataProvider dataForDeletingTagByLabel
     */
    public function testDeleteTagByLabel($useQueryString)
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneWithTags($this->user->getId());

        $entry = $entry[0];

        $tag = new Tag();
        $tag->setLabel('Awesome tag for test');
        $em->persist($tag);

        $entry->addTag($tag);

        $em->persist($entry);
        $em->flush();

        if ($useQueryString) {
            $this->client->request('DELETE', '/api/tag/label.json?tag=' . $tag->getLabel());
        } else {
            $this->client->request('DELETE', '/api/tag/label.json', ['tag' => $tag->getLabel()]);
        }

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('label', $content);
        $this->assertSame($tag->getLabel(), $content['label']);
        $this->assertSame($tag->getSlug(), $content['slug']);

        $entries = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findAllByTagId($this->user->getId(), $tag->getId());

        $this->assertCount(0, $entries);
    }

    public function testDeleteTagByLabelNotFound()
    {
        $this->client->request('DELETE', '/api/tag/label.json', ['tag' => 'does not exist']);

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteTagByLabelOtherUser()
    {
        $this->client->request('DELETE', '/api/tag/label.json', ['tag' => $this->otherUserTagLabel]);

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider dataForDeletingTagByLabel
     */
    public function testDeleteTagsByLabel($useQueryString)
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneWithTags($this->user->getId());

        $entry = $entry[0];

        $tag = new Tag();
        $tag->setLabel('Awesome tag for tagsLabel');
        $em->persist($tag);

        $tag2 = new Tag();
        $tag2->setLabel('Awesome tag for tagsLabel 2');
        $em->persist($tag2);

        $entry->addTag($tag);
        $entry->addTag($tag2);

        $em->persist($entry);
        $em->flush();

        if ($useQueryString) {
            $this->client->request('DELETE', '/api/tags/label.json?tags=' . $tag->getLabel() . ',' . $tag2->getLabel());
        } else {
            $this->client->request('DELETE', '/api/tags/label.json', ['tags' => $tag->getLabel() . ',' . $tag2->getLabel()]);
        }

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $content);

        $this->assertArrayHasKey('label', $content[0]);
        $this->assertSame($tag->getLabel(), $content[0]['label']);
        $this->assertSame($tag->getSlug(), $content[0]['slug']);

        $this->assertArrayHasKey('label', $content[1]);
        $this->assertSame($tag2->getLabel(), $content[1]['label']);
        $this->assertSame($tag2->getSlug(), $content[1]['slug']);

        $entries = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findAllByTagId($this->user->getId(), $tag->getId());

        $this->assertCount(0, $entries);

        $entries = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findAllByTagId($this->user->getId(), $tag2->getId());

        $this->assertCount(0, $entries);
    }

    public function testDeleteTagsByLabelNotFound()
    {
        $this->client->request('DELETE', '/api/tags/label.json', ['tags' => 'does not exist']);

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteTagsByLabelOtherUser()
    {
        $this->client->request('DELETE', '/api/tags/label.json', ['tags' => $this->otherUserTagLabel]);

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }
}
