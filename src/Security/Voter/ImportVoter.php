<?php

namespace Wallabag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wallabag\Entity\User;
use Wallabag\Import\AbstractImport;

class ImportVoter extends Voter
{
    public const USE_IMPORTER = 'USE_IMPORTER';

    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof AbstractImport) {
            return false;
        }

        if (!\in_array($attribute, [self::USE_IMPORTER], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        \assert($subject instanceof AbstractImport);

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::USE_IMPORTER => true === $subject->isEnabled(),
            default => false,
        };
    }
}
