<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Entity\TaggingRule;
use Wallabag\CoreBundle\Helper\RuleBasedTagger;
use Wallabag\UserBundle\Entity\User;

class RuleBasedTaggerTest extends \PHPUnit_Framework_TestCase
{
    private $rulerz;
    private $tagRepository;
    private $entryRepository;
    private $tagger;

    public function setUp()
    {
        $this->rulerz = $this->getRulerZMock();
        $this->tagRepository = $this->getTagRepositoryMock();
        $this->entryRepository = $this->getEntryRepositoryMock();

        $this->tagger = new RuleBasedTagger($this->rulerz, $this->tagRepository, $this->entryRepository);
    }

    public function testTagWithNoRule()
    {
        $entry = new Entry($this->getUser());

        $this->tagger->tag($entry);

        $this->assertTrue($entry->getTags()->isEmpty());
    }

    public function testTagWithNoMatchingRule()
    {
        $taggingRule = $this->getTaggingRule('rule as string', ['foo', 'bar']);
        $user = $this->getUser([$taggingRule]);
        $entry = new Entry($user);

        $this->rulerz
            ->expects($this->once())
            ->method('satisfies')
            ->with($entry, 'rule as string')
            ->willReturn(false);

        $this->tagger->tag($entry);

        $this->assertTrue($entry->getTags()->isEmpty());
    }

    public function testTagWithAMatchingRule()
    {
        $taggingRule = $this->getTaggingRule('rule as string', ['foo', 'bar']);
        $user = $this->getUser([$taggingRule]);
        $entry = new Entry($user);

        $this->rulerz
            ->expects($this->once())
            ->method('satisfies')
            ->with($entry, 'rule as string')
            ->willReturn(true);

        $this->tagger->tag($entry);

        $this->assertFalse($entry->getTags()->isEmpty());

        $tags = $entry->getTags();
        $this->assertSame('foo', $tags[0]->getLabel());
        $this->assertSame('bar', $tags[1]->getLabel());
    }

    public function testTagWithAMixOfMatchingRules()
    {
        $taggingRule = $this->getTaggingRule('bla bla', ['hey']);
        $otherTaggingRule = $this->getTaggingRule('rule as string', ['foo']);

        $user = $this->getUser([$taggingRule, $otherTaggingRule]);
        $entry = new Entry($user);

        $this->rulerz
            ->method('satisfies')
            ->will($this->onConsecutiveCalls(false, true));

        $this->tagger->tag($entry);

        $this->assertFalse($entry->getTags()->isEmpty());

        $tags = $entry->getTags();
        $this->assertSame('foo', $tags[0]->getLabel());
    }

    public function testWhenTheTagExists()
    {
        $taggingRule = $this->getTaggingRule('rule as string', ['foo']);
        $user = $this->getUser([$taggingRule]);
        $entry = new Entry($user);
        $tag = new Tag();

        $this->rulerz
            ->expects($this->once())
            ->method('satisfies')
            ->with($entry, 'rule as string')
            ->willReturn(true);

        $this->tagRepository
            ->expects($this->once())
            // the method `findOneByLabel` doesn't exist, EntityRepository will then call `_call` method
            // to magically call the `findOneBy` with ['label' => 'foo']
            ->method('__call')
            ->willReturn($tag);

        $this->tagger->tag($entry);

        $this->assertFalse($entry->getTags()->isEmpty());

        $tags = $entry->getTags();
        $this->assertSame($tag, $tags[0]);
    }

    public function testSameTagWithDifferentfMatchingRules()
    {
        $taggingRule = $this->getTaggingRule('bla bla', ['hey']);
        $otherTaggingRule = $this->getTaggingRule('rule as string', ['hey']);

        $user = $this->getUser([$taggingRule, $otherTaggingRule]);
        $entry = new Entry($user);

        $this->rulerz
            ->method('satisfies')
            ->willReturn(true);

        $this->tagger->tag($entry);

        $this->assertFalse($entry->getTags()->isEmpty());

        $tags = $entry->getTags();
        $this->assertCount(1, $tags);
    }

    public function testTagAllEntriesForAUser()
    {
        $taggingRule = $this->getTaggingRule('bla bla', ['hey']);

        $user = $this->getUser([$taggingRule]);

        $this->rulerz
            ->method('satisfies')
            ->willReturn(true);

        $this->rulerz
            ->method('filter')
            ->willReturn([new Entry($user), new Entry($user)]);

        $entries = $this->tagger->tagAllForUser($user);

        $this->assertCount(2, $entries);

        foreach ($entries as $entry) {
            $tags = $entry->getTags();

            $this->assertCount(1, $tags);
            $this->assertEquals('hey', $tags[0]->getLabel());
        }
    }

    private function getUser(array $taggingRules = [])
    {
        $user = new User();
        $config = new Config($user);

        $user->setConfig($config);

        foreach ($taggingRules as $rule) {
            $config->addTaggingRule($rule);
        }

        return $user;
    }

    private function getTaggingRule($rule, array $tags)
    {
        $taggingRule = new TaggingRule();
        $taggingRule->setRule($rule);
        $taggingRule->setTags($tags);

        return $taggingRule;
    }

    private function getRulerZMock()
    {
        return $this->getMockBuilder('RulerZ\RulerZ')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getTagRepositoryMock()
    {
        return $this->getMockBuilder('Wallabag\CoreBundle\Repository\TagRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getEntryRepositoryMock()
    {
        return $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
