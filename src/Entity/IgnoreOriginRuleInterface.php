<?php

namespace App\Entity;

interface IgnoreOriginRuleInterface
{
    public function getId();

    public function setRule(string $rule);

    public function getRule();
}
