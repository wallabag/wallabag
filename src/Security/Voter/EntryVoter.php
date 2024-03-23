<?php

namespace Wallabag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;

class EntryVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const RELOAD = 'RELOAD';
    public const STAR = 'STAR';

    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof Entry) {
            return false;
        }

        if (!\in_array($attribute, [self::VIEW, self::EDIT, self::RELOAD, self::STAR], true)) {
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
            case self::VIEW:
            case self::EDIT:
            case self::RELOAD:
            case self::STAR:
                return $user === $subject->getUser();
        }

        return false;
    }
}
