<?php

namespace Wallabag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Wallabag\Entity\User;

class UserVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof User) {
            return false;
        }

        if (!\in_array($attribute, [self::EDIT, self::DELETE], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        \assert($user instanceof User);

        switch ($attribute) {
            case self::EDIT:
                return $this->security->isGranted('ROLE_SUPER_ADMIN');
            case self::DELETE:
                if ($user === $subject) {
                    return false;
                }

                return $this->security->isGranted('ROLE_SUPER_ADMIN');
        }

        return false;
    }
}
