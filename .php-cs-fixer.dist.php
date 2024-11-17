<?php

$config = new PhpCsFixer\Config();

return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'combine_consecutive_unsets' => true,
        'heredoc_to_nowdoc' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'continue',
                'extra',
                'return',
                'throw',
                'use',
                'parenthesis_brace_block',
                'square_brace_block',
                'curly_brace_block',
            ],
        ],
        'no_unreachable_default_argument_value' => true,
        'no_useless_concat_operator' => false,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'php_unit_strict' => true,
        'phpdoc_order' => true,
        'phpdoc_separation' => false,
        // 'psr_autoloading' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        // We override next rule because of current @Symfony ruleSet
        // 'parameters' element is breaking PHP 7.4
        // https://cs.symfony.com/doc/rules/control_structure/trailing_comma_in_multiline.html
        // TODO: remove this configuration after dropping support of PHP 7.4
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
                'array_destructuring',
                'match',
            ],
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude([
                'node_modules',
                'vendor',
                'var',
                'web',
            ])
            ->in(__DIR__)
    )
    ->setCacheFile('.php-cs-fixer.cache')
;
