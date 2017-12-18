<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use PHPUnit\Framework\TestCase;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Helper\TagsAssigner;
use Wallabag\CoreBundle\Repository\TagRepository;
use Wallabag\UserBundle\Entity\User;

class TagsAssignerTest extends TestCase
{
    public function testAssignTagsWithArrayAndExtraSpaces()
    {
        $tagRepo = $this->getTagRepositoryMock();
        $tagsAssigner = new TagsAssigner($tagRepo);

        $entry = new Entry(new User());

        $tagsAssigner->assignTagsToEntry($entry, ['   tag1', 'tag2   ']);

        $this->assertCount(2, $entry->getTags());
        $this->assertSame('tag1', $entry->getTags()[0]->getLabel());
        $this->assertSame('tag2', $entry->getTags()[1]->getLabel());
    }

    public function testAssignTagsWithString()
    {
        $tagRepo = $this->getTagRepositoryMock();
        $tagsAssigner = new TagsAssigner($tagRepo);

        $entry = new Entry(new User());

        $tagsAssigner->assignTagsToEntry($entry, 'tag1, tag2');

        $this->assertCount(2, $entry->getTags());
        $this->assertSame('tag1', $entry->getTags()[0]->getLabel());
        $this->assertSame('tag2', $entry->getTags()[1]->getLabel());
    }

    public function testAssignTagsWithEmptyArray()
    {
        $tagRepo = $this->getTagRepositoryMock();
        $tagsAssigner = new TagsAssigner($tagRepo);

        $entry = new Entry(new User());

        $tagsAssigner->assignTagsToEntry($entry, []);

        $this->assertCount(0, $entry->getTags());
    }

    public function testAssignTagsWithEmptyString()
    {
        $tagRepo = $this->getTagRepositoryMock();
        $tagsAssigner = new TagsAssigner($tagRepo);

        $entry = new Entry(new User());

        $tagsAssigner->assignTagsToEntry($entry, '');

        $this->assertCount(0, $entry->getTags());
    }

    public function testAssignTagsAlreadyAssigned()
    {
        $tagRepo = $this->getTagRepositoryMock();
        $tagsAssigner = new TagsAssigner($tagRepo);

        $tagEntity = new Tag();
        $tagEntity->setLabel('tag1');

        $entry = new Entry(new User());
        $entry->addTag($tagEntity);

        $tagsAssigner->assignTagsToEntry($entry, 'tag1, tag2');

        $this->assertCount(2, $entry->getTags());
        $this->assertSame('tag1', $entry->getTags()[0]->getLabel());
        $this->assertSame('tag2', $entry->getTags()[1]->getLabel());
    }

    public function testAssignTagsNotFlushed()
    {
        $tagRepo = $this->getTagRepositoryMock();
        $tagRepo->expects($this->never())
            ->method('__call');

        $tagsAssigner = new TagsAssigner($tagRepo);

        $tagEntity = new Tag();
        $tagEntity->setLabel('tag1');

        $entry = new Entry(new User());

        $tagsAssigner->assignTagsToEntry($entry, 'tag1', [$tagEntity]);

        $this->assertCount(1, $entry->getTags());
        $this->assertSame('tag1', $entry->getTags()[0]->getLabel());
    }

    private function getTagRepositoryMock()
    {
        return $this->getMockBuilder(TagRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
