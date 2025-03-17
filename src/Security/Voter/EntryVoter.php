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
    public const ARCHIVE = 'ARCHIVE';
    public const SHARE = 'SHARE';
    public const UNSHARE = 'UNSHARE';
    public const EXPORT = 'EXPORT';
    public const DELETE = 'DELETE';
    public const LIST_ANNOTATIONS = 'LIST_ANNOTATIONS';
    public const CREATE_ANNOTATIONS = 'CREATE_ANNOTATIONS';
    public const TAG = 'TAG';
    public const UNTAG = 'UNTAG';

    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof Entry) {
            return false;
        }

        if (!\in_array($attribute, [self::VIEW, self::EDIT, self::RELOAD, self::STAR, self::ARCHIVE, self::SHARE, self::UNSHARE, self::EXPORT, self::DELETE, self::LIST_ANNOTATIONS, self::CREATE_ANNOTATIONS, self::TAG, self::UNTAG], true)) {
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
            case self::ARCHIVE:
            case self::SHARE:
            case self::UNSHARE:
            case self::EXPORT:
            case self::DELETE:
            case self::LIST_ANNOTATIONS:
            case self::CREATE_ANNOTATIONS:
            case self::TAG:
            case self::UNTAG:
                return $user === $subject->getUser();
        }

        return false;
    }
}
