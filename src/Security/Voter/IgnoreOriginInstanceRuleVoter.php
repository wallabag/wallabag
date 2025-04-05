<?php

namespace Wallabag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Wallabag\Entity\IgnoreOriginInstanceRule;

class IgnoreOriginInstanceRuleVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof IgnoreOriginInstanceRule) {
            return false;
        }

        if (!\in_array($attribute, [self::EDIT, self::DELETE], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            self::EDIT, self::DELETE => $this->security->isGranted('ROLE_SUPER_ADMIN'),
            default => false,
        };
    }
}
