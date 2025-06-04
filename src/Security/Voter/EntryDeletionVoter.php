<?php

namespace Wallabag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wallabag\Entity\EntryDeletion;
use Wallabag\Entity\User;

class EntryDeletionVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const LIST = 'LIST';

    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof EntryDeletion) {
            return false;
        }

        if (!\in_array($attribute, [self::VIEW, self::LIST], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        \assert($subject instanceof EntryDeletion);

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::VIEW, self::LIST => $user === $subject->getUser(),
            default => false,
        };
    }
}
