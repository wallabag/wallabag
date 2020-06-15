<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Entity\TaggingRule;
use Wallabag\CoreBundle\Helper\RuleBasedTagger;
use Wallabag\UserBundle\Entity\User;

class RuleBasedTaggerTest extends TestCase
{
    private $rulerz;
    private $tagRepository;
    private $entryRepository;
    private $tagger;
    private $logger;
    private $handler;

    public function setUp(): void
    {
        $this->rulerz = $this->getRulerZMock();
        $this->tagRepository = $this->getTagRepositoryMock();
        $this->entryRepository = $this->getEntryRepositoryMock();
        $this->logger = $this->getLogger();
        $this->handler = new TestHandler();
        $this->logger->pushHandler($this->handler);

        $this->tagger = new RuleBasedTagger($this->rulerz, $this->tagRepository, $this->entryRepository, $this->logger);
    }

    public function testTagWithNoRule()
    {
        $entry = new Entry($this->getUser());

        $this->tagger->tag($entry);

        $this->assertTrue($entry->getTags()->isEmpty());
        $records = $this->handler->getRecords();
        $this->assertCount(0, $records);
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
        $records = $this->handler->getRecords();
        $this->assertCount(0, $records);
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

        $records = $this->handler->getRecords();
        $this->assertCount(1, $records);
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
        $records = $this->handler->getRecords();
        $this->assertCount(1, $records);
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
        $records = $this->handler->getRecords();
        $this->assertCount(1, $records);
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
        $records = $this->handler->getRecords();
        $this->assertCount(2, $records);
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
            $this->assertSame('hey', $tags[0]->getLabel());
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

    private function getLogger()
    {
        return new Logger('foo');
    }
}
