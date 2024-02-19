<?php

namespace Wallabag\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use RulerZ\RulerZ;
use Wallabag\Entity\Entry;
use Wallabag\Entity\Tag;
use Wallabag\Entity\TaggingRule;
use Wallabag\Entity\User;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\TagRepository;

class RuleBasedTagger
{
    private $rulerz;
    private $tagRepository;
    private $entryRepository;
    private $logger;

    public function __construct(RulerZ $rulerz, TagRepository $tagRepository, EntryRepository $entryRepository, LoggerInterface $logger)
    {
        $this->rulerz = $rulerz;
        $this->tagRepository = $tagRepository;
        $this->entryRepository = $entryRepository;
        $this->logger = $logger;
    }

    /**
     * Add tags from rules defined by the user.
     *
     * @param Entry $entry Entry to tag
     */
    public function tag(Entry $entry)
    {
        $rules = $this->getRulesForUser($entry->getUser());

        $clonedEntry = $this->fixEntry($entry);

        foreach ($rules as $rule) {
            if (!$this->rulerz->satisfies($clonedEntry, $rule->getRule())) {
                continue;
            }

            $this->logger->info('Matching rule.', [
                'rule' => $rule->getRule(),
                'tags' => $rule->getTags(),
            ]);

            foreach ($rule->getTags() as $label) {
                $tag = $this->getTag($label);

                $entry->addTag($tag);
            }
        }
    }

    /**
     * Apply all the tagging rules defined by a user on its entries.
     *
     * @return array<Entry> A list of modified entries
     */
    public function tagAllForUser(User $user)
    {
        $rules = $this->getRulesForUser($user);
        $entriesToUpdate = [];
        $tagsCache = [];

        $entries = $this->entryRepository
            ->getBuilderForAllByUser($user->getId())
            ->getQuery()
            ->getResult();

        foreach ($entries as $entry) {
            $clonedEntry = $this->fixEntry($entry);

            foreach ($rules as $rule) {
                if (!$this->rulerz->satisfies($clonedEntry, $rule->getRule())) {
                    continue;
                }

                foreach ($rule->getTags() as $label) {
                    // avoid new tag duplicate by manually caching them
                    if (!isset($tagsCache[$label])) {
                        $tagsCache[$label] = $this->getTag($label);
                    }

                    $tag = $tagsCache[$label];

                    $entry->addTag($tag);

                    $entriesToUpdate[] = $entry;
                }
            }
        }

        return $entriesToUpdate;
    }

    /**
     * Fetch a tag.
     *
     * @param string $label The tag's label
     *
     * @return Tag
     */
    private function getTag($label)
    {
        $label = mb_convert_case($label, \MB_CASE_LOWER);
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
     * @return ArrayCollection<TaggingRule>
     */
    private function getRulesForUser(User $user)
    {
        return $user->getConfig()->getTaggingRules();
    }

    /**
     * Update reading time on the fly to match the proper words per minute from the user.
     */
    private function fixEntry(Entry $entry)
    {
        $clonedEntry = clone $entry;
        $clonedEntry->setReadingTime($entry->getReadingTime() / $entry->getUser()->getConfig()->getReadingSpeed() * 200);

        return $clonedEntry;
    }
}
