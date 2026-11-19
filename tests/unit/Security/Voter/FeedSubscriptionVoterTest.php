<?php

namespace Wallabag\Tests\Unit\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Wallabag\Entity\FeedUrl;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\FeedSubscriptionVoter;

class FeedSubscriptionVoterTest extends TestCase
{
    /** @dataProvider permissions */
    public function testOnlyTheOwnerCanUseEachPermission(string $permission): void
    {
        $owner = new User();
        $someoneElse = new User();
        $admin = new User();
        $admin->setRoles(['ROLE_SUPER_ADMIN']);
        $subject = new FeedUrl($owner);
        $voter = new FeedSubscriptionVoter();

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($this->token($owner), $subject, [$permission]));
        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($this->token($someoneElse), $subject, [$permission]));
        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($this->token($admin), $subject, [$permission]));
        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($this->token(null), $subject, [$permission]));
    }

    /** @dataProvider permissions */
    public function testAbstainsForUnsupportedSubjectsAndAttributes(string $permission): void
    {
        $owner = new User();
        $subject = new FeedUrl($owner);
        $voter = new FeedSubscriptionVoter();
        $token = $this->token($owner);

        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, new \stdClass(), [$permission]));
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, $subject, ['INVALID']));
    }

    public function permissions(): iterable
    {
        yield 'edit' => ['EDIT'];
    }

    private function token(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }
}
