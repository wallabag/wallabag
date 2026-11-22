<?php

use TwigCsFixer\Config\Config;
use TwigCsFixer\File\Finder;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\Twig;

$finder = (new Finder())->in(__DIR__ . '/templates');
$ruleset = new Ruleset();
// Keep the rules focused on Twig tags so literal template whitespace is preserved.
$ruleset
    ->addStandard(new Twig())
;

return (new Config())
    ->setFinder($finder)
    ->setRuleset($ruleset)
    ->setCacheFile(null)
;
