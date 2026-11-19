<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use RulerZ\RulerZ;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\IgnoreOriginInstanceRule;
use Wallabag\CoreBundle\Entity\IgnoreOriginUserRule;
use Wallabag\CoreBundle\Helper\RuleBasedIgnoreOriginProcessor;
use Wallabag\CoreBundle\Repository\IgnoreOriginInstanceRuleRepository;
use Wallabag\UserBundle\Entity\User;

class RuleBasedIgnoreOriginProcessorTest extends TestCase
{
    private $rulerz;
    private $processor;
    private $validator;
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

        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->validator->method('validate')->willReturnCallback(static function ($rule) {
            if ('invalid rule' !== $rule->getRule()) {
                return new ConstraintViolationList();
            }

            return new ConstraintViolationList([new ConstraintViolation('invalid', null, [], $rule, 'rule', $rule->getRule())]);
        });

        $this->processor = new RuleBasedIgnoreOriginProcessor($this->rulerz, $this->logger, $this->ignoreOriginInstanceRuleRepository, $this->validator);
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

    public function testProcessIgnoresInvalidStoredRule()
    {
        $user = $this->getUser([$this->getIgnoreOriginUserRule('invalid rule')]);
        $entry = new Entry($user);
        $entry->setUrl('http://example.com/hello-world');

        $this->ignoreOriginInstanceRuleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $this->rulerz
            ->expects($this->never())
            ->method('satisfies');

        $this->assertFalse($this->processor->process($entry));
        $records = $this->handler->getRecords();
        $this->assertSame('Ignoring invalid ignore origin rule.', $records[0]['message']);
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
