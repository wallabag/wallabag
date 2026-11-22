<?php

namespace Wallabag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\User;

class FeedItemVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof FeedItem
            && \in_array($attribute, ['DISMISS', 'SAVE'], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        \assert($subject instanceof FeedItem);

        $user = $token->getUser();

        return $user instanceof User && $user === $subject->getFeed()->getUser();
    }
}
