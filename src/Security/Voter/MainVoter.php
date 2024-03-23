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
    public const LIST_SITE_CREDENTIALS = 'LIST_SITE_CREDENTIALS';
    public const CREATE_SITE_CREDENTIALS = 'CREATE_SITE_CREDENTIALS';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (null !== $subject) {
            return false;
        }

        if (!\in_array($attribute, [self::LIST_ENTRIES, self::CREATE_ENTRIES, self::EDIT_ENTRIES, self::LIST_SITE_CREDENTIALS, self::CREATE_SITE_CREDENTIALS], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::LIST_ENTRIES:
            case self::CREATE_ENTRIES:
            case self::EDIT_ENTRIES:
            case self::LIST_SITE_CREDENTIALS:
            case self::CREATE_SITE_CREDENTIALS:
                return $this->security->isGranted('ROLE_USER');
        }

        return false;
    }
}
