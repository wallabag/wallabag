<?php

namespace Tests\Wallabag\Helper;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use RulerZ\RulerZ;
use Wallabag\Entity\Config;
use Wallabag\Entity\Entry;
use Wallabag\Entity\IgnoreOriginInstanceRule;
use Wallabag\Entity\IgnoreOriginUserRule;
use Wallabag\Entity\User;
use Wallabag\Helper\RuleBasedIgnoreOriginProcessor;
use Wallabag\Repository\IgnoreOriginInstanceRuleRepository;

class RuleBasedIgnoreOriginProcessorTest extends TestCase
{
    private $rulerz;
    private $processor;
    private $ignoreOriginInstanceRuleRepository;
    private $logger;
    private $handler;

    protected function setUp(): void
    {
        $this->rulerz = $this->getRulerZMock();
        $this->logger = $this->getLogger();
        $this->ignoreOriginInstanceRuleRepository = $this->getIgnoreOriginInstanceRuleRepositoryMock();
        $this->handler = new TestHandler();
        $this->logger->pushHandler($this->handler);

        $this->processor = new RuleBasedIgnoreOriginProcessor($this->rulerz, $this->logger, $this->ignoreOriginInstanceRuleRepository);
    }

    public function testProcessWithNoRule()
    {
        $user = $this->getUser();
        $entry = new Entry($user);
        $entry->setUrl('http://example.com/hello-world');

        $this->ignoreOriginInstanceRuleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $this->rulerz
            ->expects($this->never())
            ->method('satisfies');

        $result = $this->processor->process($entry);

        $this->assertFalse($result);
    }

    public function testProcessWithNoMatchingRule()
    {
        $userRule = $this->getIgnoreOriginUserRule('rule as string');
        $user = $this->getUser([$userRule]);
        $entry = new Entry($user);
        $entry->setUrl('http://example.com/hello-world');

        $this->ignoreOriginInstanceRuleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $this->rulerz
            ->expects($this->once())
            ->method('satisfies')
            ->willReturn(false);

        $result = $this->processor->process($entry);

        $this->assertFalse($result);
    }

    public function testProcessWithAMatchingRule()
    {
        $userRule = $this->getIgnoreOriginUserRule('rule as string');
        $user = $this->getUser([$userRule]);
        $entry = new Entry($user);
        $entry->setUrl('http://example.com/hello-world');

        $this->ignoreOriginInstanceRuleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $this->rulerz
            ->expects($this->once())
            ->method('satisfies')
            ->willReturn(true);

        $result = $this->processor->process($entry);

        $this->assertTrue($result);
    }

    public function testProcessWithAMixOfMatchingRules()
    {
        $userRule = $this->getIgnoreOriginUserRule('rule as string');
        $anotherUserRule = $this->getIgnoreOriginUserRule('another rule as string');
        $user = $this->getUser([$userRule, $anotherUserRule]);
        $entry = new Entry($user);
        $entry->setUrl('http://example.com/hello-world');

        $this->ignoreOriginInstanceRuleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $this->rulerz
            ->method('satisfies')
            ->will($this->onConsecutiveCalls(false, true));

        $result = $this->processor->process($entry);

        $this->assertTrue($result);
    }

    public function testProcessWithInstanceRules()
    {
        $user = $this->getUser();
        $entry = new Entry($user);
        $entry->setUrl('http://example.com/hello-world');

        $instanceRule = $this->getIgnoreOriginInstanceRule('rule as string');
        $this->ignoreOriginInstanceRuleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$instanceRule]);

        $this->rulerz
            ->expects($this->once())
            ->method('satisfies')
            ->willReturn(true);

        $result = $this->processor->process($entry);

        $this->assertTrue($result);
    }

    public function testProcessWithMixedRules()
    {
        $userRule = $this->getIgnoreOriginUserRule('rule as string');
        $user = $this->getUser([$userRule]);
        $entry = new Entry($user);
        $entry->setUrl('http://example.com/hello-world');

        $instanceRule = $this->getIgnoreOriginInstanceRule('rule as string');
        $this->ignoreOriginInstanceRuleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$instanceRule]);

        $this->rulerz
            ->method('satisfies')
            ->will($this->onConsecutiveCalls(false, true));

        $result = $this->processor->process($entry);

        $this->assertTrue($result);
    }

    private function getUser(array $ignoreOriginRules = [])
    {
        $user = new User();
        $config = new Config($user);

        $user->setConfig($config);

        foreach ($ignoreOriginRules as $rule) {
            $config->addIgnoreOriginRule($rule);
        }

        return $user;
    }

    private function getIgnoreOriginUserRule($rule)
    {
        $ignoreOriginUserRule = new IgnoreOriginUserRule();
        $ignoreOriginUserRule->setRule($rule);

        return $ignoreOriginUserRule;
    }

    private function getIgnoreOriginInstanceRule($rule)
    {
        $ignoreOriginInstanceRule = new IgnoreOriginInstanceRule();
        $ignoreOriginInstanceRule->setRule($rule);

        return $ignoreOriginInstanceRule;
    }

    private function getRulerZMock()
    {
        return $this->getMockBuilder(RulerZ::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getIgnoreOriginInstanceRuleRepositoryMock()
    {
        return $this->getMockBuilder(IgnoreOriginInstanceRuleRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getLogger()
    {
        return new Logger('foo');
    }
}
