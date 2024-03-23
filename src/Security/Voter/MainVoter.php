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

        if (!\in_array($attribute, [self::LIST_ENTRIES, self::CREATE_ENTRIES, self::EDIT_ENTRIES], true)) {
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
                return $this->security->isGranted('ROLE_USER');
        }

        return false;
    }
}
