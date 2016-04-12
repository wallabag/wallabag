<?php

namespace Wallabag\CoreBundle\Helper;

use RulerZ\RulerZ;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Repository\EntryRepository;
use Wallabag\CoreBundle\Repository\TagRepository;
use Wallabag\UserBundle\Entity\User;

class RuleBasedTagger
{
    private $rulerz;
    private $tagRepository;
    private $entryRepository;

    public function __construct(RulerZ $rulerz, TagRepository $tagRepository, EntryRepository $entryRepository)
    {
        $this->rulerz = $rulerz;
        $this->tagRepository = $tagRepository;
        $this->entryRepository = $entryRepository;
    }

    /**
     * Add tags from rules defined by the user.
     *
     * @param Entry $entry Entry to tag.
     */
    public function tag(Entry $entry)
    {
        $rules = $this->getRulesForUser($entry->getUser());

        foreach ($rules as $rule) {
            if (!$this->rulerz->satisfies($entry, $rule->getRule())) {
                continue;
            }

            foreach ($rule->getTags() as $label) {
                $tag = $this->getTag($label);

                $entry->addTag($tag);
            }
        }
    }

    /**
     * Apply all the tagging rules defined by a user on its entries.
     *
     * @param User $user
     *
     * @return array<Entry> A list of modified entries.
     */
    public function tagAllForUser(User $user)
    {
        $rules = $this->getRulesForUser($user);
        $entries = [];

        foreach ($rules as $rule) {
            $qb = $this->entryRepository->getBuilderForAllByUser($user->getId());
            $entries = $this->rulerz->filter($qb, $rule->getRule());

            foreach ($entries as $entry) {
                foreach ($rule->getTags() as $label) {
                    $tag = $this->getTag($label);

                    $entry->addTag($tag);
                }
            }
        }

        return $entries;
    }

    /**
     * Fetch a tag.
     *
     * @param string $label The tag's label.
     *
     * @return Tag
     */
    private function getTag($label)
    {
        $tag = $this->tagRepository->findOneByLabel($label);

        if (!$tag) {
            $tag = new Tag();
            $tag->setLabel($label);
        }

        return $tag;
    }

    /**
     * Retrieves the tagging rules for a given user.
     *
     * @param User $user
     *
     * @return array<TaggingRule>
     */
    private function getRulesForUser(User $user)
    {
        return $user->getConfig()->getTaggingRules();
    }
}
