<?php

namespace Wallabag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wallabag\Entity\FeedUrl;
use Wallabag\Entity\User;

class FeedSubscriptionVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof FeedUrl
            && \in_array($attribute, ['EDIT', 'LIST_ITEMS', 'REFRESH'], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        \assert($subject instanceof FeedUrl);

        $user = $token->getUser();

        return $user instanceof User && $user === $subject->getUser();
    }
}
