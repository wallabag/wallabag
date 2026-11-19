<?php

namespace Wallabag\CoreBundle\Helper;

use Psr\Log\LoggerInterface;
use RulerZ\RulerZ;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Repository\IgnoreOriginInstanceRuleRepository;

class RuleBasedIgnoreOriginProcessor
{
    protected $rulerz;
    protected $logger;
    protected $ignoreOriginInstanceRuleRepository;
    protected $validator;

    public function __construct(RulerZ $rulerz, LoggerInterface $logger, IgnoreOriginInstanceRuleRepository $ignoreOriginInstanceRuleRepository, ValidatorInterface $validator)
    {
        $this->rulerz = $rulerz;
        $this->logger = $logger;
        $this->ignoreOriginInstanceRuleRepository = $ignoreOriginInstanceRuleRepository;
        $this->validator = $validator;
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
            if (0 < \count($this->validator->validate($rule))) {
                $this->logger->warning('Ignoring invalid ignore origin rule.', ['rule' => $rule->getRule()]);

                continue;
            }

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
