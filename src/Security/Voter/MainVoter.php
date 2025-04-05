<?php

namespace Wallabag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class MainVoter extends Voter
{
    public const LIST_ENTRIES = 'LIST_ENTRIES';
    public const CREATE_ENTRIES = 'CREATE_ENTRIES';
    public const EDIT_ENTRIES = 'EDIT_ENTRIES';
    public const EXPORT_ENTRIES = 'EXPORT_ENTRIES';
    public const IMPORT_ENTRIES = 'IMPORT_ENTRIES';
    public const DELETE_ENTRIES = 'DELETE_ENTRIES';
    public const LIST_TAGS = 'LIST_TAGS';
    public const CREATE_TAGS = 'CREATE_TAGS';
    public const DELETE_TAGS = 'DELETE_TAGS';
    public const LIST_SITE_CREDENTIALS = 'LIST_SITE_CREDENTIALS';
    public const CREATE_SITE_CREDENTIALS = 'CREATE_SITE_CREDENTIALS';
    public const EDIT_CONFIG = 'EDIT_CONFIG';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (null !== $subject) {
            return false;
        }

        if (!\in_array($attribute, [self::LIST_ENTRIES, self::CREATE_ENTRIES, self::EDIT_ENTRIES, self::EXPORT_ENTRIES, self::IMPORT_ENTRIES, self::DELETE_ENTRIES, self::LIST_TAGS, self::CREATE_TAGS, self::DELETE_TAGS, self::LIST_SITE_CREDENTIALS, self::CREATE_SITE_CREDENTIALS, self::EDIT_CONFIG], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            self::LIST_ENTRIES, self::CREATE_ENTRIES, self::EDIT_ENTRIES, self::EXPORT_ENTRIES, self::IMPORT_ENTRIES, self::DELETE_ENTRIES, self::LIST_TAGS, self::CREATE_TAGS, self::DELETE_TAGS, self::LIST_SITE_CREDENTIALS, self::CREATE_SITE_CREDENTIALS, self::EDIT_CONFIG => $this->security->isGranted('ROLE_USER'),
            default => false,
        };
    }
}
