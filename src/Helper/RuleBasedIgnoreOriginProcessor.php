<?php

namespace Wallabag\Helper;

use Psr\Log\LoggerInterface;
use RulerZ\RulerZ;
use Wallabag\Entity\Entry;
use Wallabag\Repository\IgnoreOriginInstanceRuleRepository;

class RuleBasedIgnoreOriginProcessor
{
    public function __construct(
        protected RulerZ $rulerz,
        protected LoggerInterface $logger,
        protected IgnoreOriginInstanceRuleRepository $ignoreOriginInstanceRuleRepository,
    ) {
    }

    /**
     * @param Entry $entry Entry to process
     *
     * @return bool
     */
    public function process(Entry $entry)
    {
        $url = $entry->getUrl();
        $userRules = $entry->getUser()->getConfig()->getIgnoreOriginRules()->toArray();
        $rules = array_merge($this->ignoreOriginInstanceRuleRepository->findAll(), $userRules);

        $parsed_url = parse_url($url);
        // We add the former url as a new key _all for pattern matching
        $parsed_url['_all'] = $url;

        foreach ($rules as $rule) {
            if ($this->rulerz->satisfies($parsed_url, $rule->getRule())) {
                $this->logger->info('Origin url matching ignore rule.', [
                    'rule' => $rule->getRule(),
                ]);

                return true;
            }
        }

        return false;
    }
}
