<?php

namespace Wallabag\CoreBundle\Helper;

use RulerZ\RulerZ;

use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Repository\TagRepository;
use Wallabag\UserBundle\Entity\User;

class RuleBasedTagger
{
    private $rulerz;
    private $tagRepository;

    public function __construct(RulerZ $rulerz, TagRepository $tagRepository)
    {
        $this->rulerz        = $rulerz;
        $this->tagRepository = $tagRepository;
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
            if (!$this->rulerz->satisfies($entry, $rule['rule'])) {
                continue;
            }

            foreach ($rule['tags'] as $label) {
                $tag = $this->getTag($entry->getUser(), $label);

                $entry->addTag($tag);
            }
        }
    }

    /**
     * Fetch a tag for a user.
     *
     * @param User   $user
     * @param string $label The tag's label.
     *
     * @return Tag
     */
    private function getTag(User $user, $label)
    {
        $tag = $this->tagRepository->findOneByLabelAndUserId($label, $user->getId());

        if (!$tag) {
            $tag = new Tag($user);
            $tag->setLabel($label);
        }

        return $tag;
    }

    private function getRulesForUser(User $user)
    {
        return [
            [
                'rule' => 'domainName = "github.com"',
                'tags' => ['github'],
            ],
            [
                'rule' => 'readingTime >= 15',
                'tags' => ['long reading'],
            ],
            [
                'rule' => 'readingTime <= 3 ',
                'tags' => ['short reading'],
            ],
        ];
    }
}
