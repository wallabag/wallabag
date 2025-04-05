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
    public const LIST_TAGS = 'LIST_TAGS';
    public const TAG = 'TAG';
    public const UNTAG = 'UNTAG';

    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof Entry) {
            return false;
        }

        if (!\in_array($attribute, [self::VIEW, self::EDIT, self::RELOAD, self::STAR, self::ARCHIVE, self::SHARE, self::UNSHARE, self::EXPORT, self::DELETE, self::LIST_ANNOTATIONS, self::CREATE_ANNOTATIONS, self::LIST_TAGS, self::TAG, self::UNTAG], true)) {
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

        return match ($attribute) {
            self::VIEW, self::EDIT, self::RELOAD, self::STAR, self::ARCHIVE, self::SHARE, self::UNSHARE, self::EXPORT, self::DELETE, self::LIST_ANNOTATIONS, self::CREATE_ANNOTATIONS, self::LIST_TAGS, self::TAG, self::UNTAG => $user === $subject->getUser(),
            default => false,
        };
    }
}
