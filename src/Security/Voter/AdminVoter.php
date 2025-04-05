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
    public const LIST_IGNORE_ORIGIN_INSTANCE_RULES = 'LIST_IGNORE_ORIGIN_INSTANCE_RULES';
    public const CREATE_IGNORE_ORIGIN_INSTANCE_RULES = 'CREATE_IGNORE_ORIGIN_INSTANCE_RULES';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!\in_array($attribute, [self::LIST_USERS, self::CREATE_USERS, self::LIST_IGNORE_ORIGIN_INSTANCE_RULES, self::CREATE_IGNORE_ORIGIN_INSTANCE_RULES], true)) {
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

        return match ($attribute) {
            self::LIST_USERS, self::CREATE_USERS, self::LIST_IGNORE_ORIGIN_INSTANCE_RULES, self::CREATE_IGNORE_ORIGIN_INSTANCE_RULES => $this->security->isGranted('ROLE_SUPER_ADMIN'),
            default => false,
        };
    }
}
