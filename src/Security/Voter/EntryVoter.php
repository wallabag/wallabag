<?php

namespace Wallabag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;

class EntryVoter extends Voter
{
    public const EDIT = 'EDIT';

    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof Entry) {
            return false;
        }

        if (!\in_array($attribute, [self::EDIT], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        \assert($subject instanceof Entry);

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $user === $subject->getUser();
        }

        return false;
    }
}
