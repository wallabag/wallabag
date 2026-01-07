<?php

namespace Tests\Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Entry;
use Wallabag\Entity\Tag;
use Wallabag\Entity\User;

/**
 * @group Tag
 */
class TagControllerTest extends WallabagTestCase
{
    public $tagName = 'opensource';
    public $caseTagName = 'OpenSource';

    public function testList()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/tag/list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testAddTagToEntry()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/foo');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $form = $crawler->filter('form[name=tag]')->form();

        $data = [
            'tag[label]' => $this->caseTagName,
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        // be sure to reload the entry
        $entry = $this->getEntityManager()->getRepository(Entry::class)->find($entry->getId());
        $this->assertCount(1, $entry->getTags());
        $this->assertContains($this->tagName, $entry->getTagsLabel());

        // tag already exists and already assigned
        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $entry = $this->getEntityManager()->getRepository(Entry::class)->find($entry->getId());
        $this->assertCount(1, $entry->getTags());

        // tag already exists but still not assigned to this entry
        $data = [
            'tag[label]' => 'foo bar',
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $entry = $this->getEntityManager()->getRepository(Entry::class)->find($entry->getId());
        $this->assertCount(2, $entry->getTags());
    }

    public function testAddMultipleTagToEntry()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $entry = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/entry2', $this->getLoggedInUserId());

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $form = $crawler->filter('form[name=tag]')->form();

        $data = [
            'tag[label]' => 'foo2, Bar2',
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $newEntry = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry->getId());

        $tags = $newEntry->getTags()->toArray();
        foreach ($tags as $key => $tag) {
            $tags[$key] = $tag->getLabel();
        }

        $this->assertGreaterThanOrEqual(2, \count($tags));
        $this->assertNotFalse(array_search('foo2', $tags, true), 'Tag foo2 is assigned to the entry');
        $this->assertNotFalse(array_search('bar2', $tags, true), 'Tag bar2 is assigned to the entry');
    }

    public function testRemoveTagFromEntry()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $tag = new Tag();
        $tag->setLabel($this->tagName);
        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/foo');
        $entry->addTag($tag);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        // We make a first request to set an history and test redirection after tag deletion
        $crawler = $client->request('GET', '/view/' . $entry->getId());
        $entryUri = $client->getRequest()->getRequestUri();

        $form = $crawler->filter('form[action^="/remove-tag/' . $entry->getId() . '/' . $tag->getId() . '"]')->form();
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame($entryUri, $client->getResponse()->getTargetUrl());

        // re-retrieve the entry to be sure to get fresh data from database (mostly for tags)
        $entry = $this->getEntityManager()->getRepository(Entry::class)->find($entry->getId());
        $this->assertNotContains($this->tagName, $entry->getTagsLabel());

        $client->request('GET', '/view/' . $entry->getId());
        $this->assertStringNotContainsString('/remove-tag/' . $entry->getId() . '/' . $tag->getId(), $client->getResponse()->getContent());

        $tag = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Tag::class)
            ->findOneByLabel($this->tagName);

        $this->assertNull($tag, $this->tagName . ' was removed because it begun an orphan tag');
    }

    public function testRemoveTag()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $tag = new Tag();
        $tag->setLabel($this->tagName);

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/foo');
        $entry->addTag($tag);
        $this->getEntityManager()->persist($entry);

        $entry2 = new Entry($this->getLoggedInUser());
        $entry2->setUrl('http://0.0.0.0/bar');
        $entry2->addTag($tag);
        $this->getEntityManager()->persist($entry2);
        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/tag/list');
        $form = $crawler->filter('#tag-' . $tag->getId())->selectButton('delete')->form();
        $client->submit($form);

        $tag = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Tag::class)
            ->findOneByLabel($this->tagName);

        $this->assertNull($tag, $this->tagName . ' was removed because it begun an orphan tag');

        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneByUserName('admin');

        $entry = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/foo', $user->getId());

        $entry2 = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/bar', $user->getId());

        $this->assertEmpty($entry->getTagsLabel());
        $this->assertEmpty($entry2->getTagsLabel());
    }

    public function testShowEntriesForTagAction()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $em = $client->getContainer()
            ->get(EntityManagerInterface::class);

        $tag = new Tag();
        $tag->setLabel($this->tagName);

        $entry = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/entry4', $this->getLoggedInUserId());

        $tag->addEntry($entry);

        $em->persist($entry);
        $em->persist($tag);
        $em->flush();

        $tag = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Tag::class)
            ->findOneByEntryAndTagLabel($entry, $this->tagName);

        $crawler = $client->request('GET', '/tag/list/' . $tag->getSlug());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('[id*="entry-"]'));

        $entry->removeTag($tag);
        $em->remove($tag);
        $em->flush();
    }

    public function testRenameTagUsingTheFormInsideTagList()
    {
        $newTagLabel = 'rename label';

        $this->logInAs('admin');
        $client = $this->getTestClient();

        $tag = new Tag();
        $tag->setLabel($this->tagName);

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/foo');
        $entry->addTag($tag);
        $this->getEntityManager()->persist($entry);

        $entry2 = new Entry($this->getLoggedInUser());
        $entry2->setUrl('http://0.0.0.0/bar');
        $entry2->addTag($tag);
        $this->getEntityManager()->persist($entry2);

        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        // We make a first request to set an history and test redirection after tag deletion
        $crawler = $client->request('GET', '/tag/list');
        $form = $crawler->filter('#tag-' . $tag->getId() . ' form')->form();

        $data = [
            'tag[label]' => $newTagLabel,
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.tag.notice.tag_renamed', $crawler->filter('body')->extract(['_text'])[0]);

        $freshEntry = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry->getId());

        $freshEntry2 = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry2->getId());

        $tags = [];

        $tagsFromEntry = $freshEntry->getTags()->toArray();
        foreach ($tagsFromEntry as $key => $item) {
            $tags[$key] = $item->getLabel();
        }

        $tagsFromEntry2 = $freshEntry2->getTags()->toArray();
        foreach ($tagsFromEntry2 as $key => $item) {
            $tags[$key] = $item->getLabel();
        }

        $this->assertFalse(array_search($tag->getLabel(), $tags, true), 'Previous tag is not attach to entries anymore.');

        $newTag = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Tag::class)
            ->findByLabel($newTagLabel);

        $this->assertCount(1, $newTag, 'New tag exists.');

        $this->assertTrue($newTag[0]->hasEntry($freshEntry), 'New tag is assigned to the entry.');
        $this->assertTrue($newTag[0]->hasEntry($freshEntry2), 'New tag is assigned to the entry2.');
    }

    public function testRenameTagWithSameLabel()
    {
        $tagLabel = 'same label';
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $tag = new Tag();
        $tag->setLabel($tagLabel);

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/foobar');
        $entry->addTag($tag);
        $this->getEntityManager()->persist($entry);

        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        // We make a first request to set an history and test redirection after tag deletion
        $crawler = $client->request('GET', '/tag/list');
        $form = $crawler->filter('#tag-' . $tag->getId() . ' form')->form();

        $data = [
            'tag[label]' => $tagLabel,
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringNotContainsString('flashes.tag.notice.tag_renamed', $crawler->filter('body')->extract(['_text'])[0]);

        $freshEntry = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry->getId());

        $tags = [];

        $tagsFromEntry = $freshEntry->getTags()->toArray();
        foreach ($tagsFromEntry as $key => $item) {
            $tags[$key] = $item->getLabel();
        }

        $this->assertNotFalse(array_search($tag->getLabel(), $tags, true), 'Tag is still assigned to the entry.');

        $newTag = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Tag::class)
            ->findByLabel($tagLabel);

        $this->assertCount(1, $newTag);
        $this->assertSame($tag->getId(), $newTag[0]->getId(), 'Tag is unchanged.');

        $this->assertTrue($newTag[0]->hasEntry($freshEntry), 'Tag is still assigned to the entry.');
    }

    public function testRenameTagWithSameLabelDifferentCase()
    {
        $tagLabel = 'same label';
        $newTagLabel = 'saMe labEl';
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $tag = new Tag();
        $tag->setLabel($tagLabel);

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/foobar');
        $entry->addTag($tag);
        $this->getEntityManager()->persist($entry);

        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        // We make a first request to set an history and test redirection after tag deletion
        $crawler = $client->request('GET', '/tag/list');
        $form = $crawler->filter('#tag-' . $tag->getId() . ' form')->form();

        $data = [
            'tag[label]' => $newTagLabel,
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringNotContainsString('flashes.tag.notice.tag_renamed', $crawler->filter('body')->extract(['_text'])[0]);

        $freshEntry = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry->getId());

        $tags = [];

        $tagsFromEntry = $freshEntry->getTags()->toArray();
        foreach ($tagsFromEntry as $key => $item) {
            $tags[$key] = $item->getLabel();
        }

        $this->assertFalse(array_search($newTagLabel, $tags, true));

        $tagFromRepo = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Tag::class)
            ->findByLabel($tagLabel);

        $newTagFromRepo = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Tag::class)
            ->findByLabel($newTagLabel);

        $this->assertCount(0, $newTagFromRepo);
        $this->assertCount(1, $tagFromRepo);

        $this->assertSame($tag->getId(), $tagFromRepo[0]->getId(), 'Tag is unchanged.');

        $this->assertTrue($tagFromRepo[0]->hasEntry($freshEntry), 'Tag is still assigned to the entry.');
    }

    public function testRenameTagWithExistingLabel()
    {
        $tagLabel = 'existing label';
        $previousTagLabel = 'previous label';
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $tag = new Tag();
        $tag->setLabel($tagLabel);

        $previousTag = new Tag();
        $previousTag->setLabel($previousTagLabel);

        $entry1 = new Entry($this->getLoggedInUser());
        $entry1->setUrl('http://0.0.0.0/foobar');
        $entry1->addTag($previousTag);
        $this->getEntityManager()->persist($entry1);

        $entry2 = new Entry($this->getLoggedInUser());
        $entry2->setUrl('http://0.0.0.0/baz');
        $entry2->addTag($tag);
        $this->getEntityManager()->persist($entry2);

        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        // We make a first request to set an history and test redirection after tag deletion
        $crawler = $client->request('GET', '/tag/list');
        $form = $crawler->filter('#tag-' . $previousTag->getId() . ' form')->form();

        $data = [
            'tag[label]' => $tagLabel,
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringNotContainsString('flashes.tag.notice.tag_renamed', $crawler->filter('body')->extract(['_text'])[0]);

        $freshEntry1 = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry1->getId());

        $freshEntry2 = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry2->getId());

        $tagFromRepo = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Tag::class)
            ->findByLabel($tagLabel);

        $previousTagFromRepo = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Tag::class)
            ->findByLabel($previousTagLabel);

        $this->assertCount(1, $tagFromRepo);

        $this->assertTrue($tagFromRepo[0]->hasEntry($freshEntry1));
        $this->assertTrue($tagFromRepo[0]->hasEntry($freshEntry2), 'Tag is assigned to the entry.');
        $this->assertFalse($previousTagFromRepo[0]->hasEntry($freshEntry1));
    }

    public function testAddUnicodeTagLabel()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/tag-caché');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $form = $crawler->filter('form[name=tag]')->form();

        $data = [
            'tag[label]' => 'cache',
        ];

        $client->submit($form, $data);

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $form = $crawler->filter('form[name=tag]')->form();

        $data = [
            'tag[label]' => 'caché',
        ];

        $client->submit($form, $data);

        $newEntry = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry->getId());

        $tags = $newEntry->getTags()->toArray();
        foreach ($tags as $key => $tag) {
            $tags[$key] = $tag->getLabel();
        }

        $this->assertGreaterThanOrEqual(2, \count($tags));
        $this->assertNotFalse(array_search('cache', $tags, true), 'Tag cache is assigned to the entry');
        $this->assertNotFalse(array_search('caché', $tags, true), 'Tag caché is assigned to the entry');
    }

    public function testAssignTagsOnSearchResults()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        // Search on unread list
        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => 'title',
        ];

        $crawler = $client->submit($form, $data);

        $client->submit($crawler->selectButton('entry.list.assign_search_tag')->form());
        $client->followRedirect();

        $entries = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->getBuilderForSearchByUser($this->getLoggedInUserId(), 'title', 'unread')
            ->getQuery()->getResult();

        foreach ($entries as $entry) {
            $tags = $entry->getTagsLabel();

            $this->assertContains('title', $tags);
        }

        $tag = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Tag::class)
            ->findByLabelsAndUser(['title'], $this->getLoggedInUserId());

        $this->assertCount(1, $tag);
    }
}
