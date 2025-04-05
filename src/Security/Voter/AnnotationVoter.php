<?php

namespace Wallabag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wallabag\Entity\Annotation;
use Wallabag\Entity\User;

class AnnotationVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof Annotation) {
            return false;
        }

        if (!\in_array($attribute, [self::EDIT, self::DELETE], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        \assert($subject instanceof Annotation);

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::EDIT, self::DELETE => $subject->getUser() === $user,
            default => false,
        };
    }
}
