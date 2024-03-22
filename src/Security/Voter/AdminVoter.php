<?php

namespace Wallabag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Wallabag\Entity\User;

class AdminVoter extends Voter
{
    public const LIST_USERS = 'LIST_USERS';
    public const CREATE_USERS = 'CREATE_USERS';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!\in_array($attribute, [self::LIST_USERS, self::CREATE_USERS], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::LIST_USERS:
            case self::CREATE_USERS:
                return $this->security->isGranted('ROLE_SUPER_ADMIN');
        }

        return false;
    }
}
