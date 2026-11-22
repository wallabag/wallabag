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
        'blank_line_before_statement' => [
            'statements' => ['do', 'for', 'foreach', 'if', 'return', 'switch', 'try', 'while'],
        ],
        'class_attributes_separation' => [
            'elements' => ['const' => 'none', 'method' => 'one', 'property' => 'one'],
        ],
        'combine_consecutive_unsets' => true,
        'error_suppression' => [
            // Keep the notices emitted by the legacy web entry points visible.
            'mute_deprecation_error' => false,
        ],
        'heredoc_to_nowdoc' => true,
        'method_argument_space' => [
            'after_heredoc' => true,
            'on_multiline' => 'ensure_fully_multiline',
        ],
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
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters'],
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->ignoreVCSIgnored(true)
            ->exclude([
                'app/build',
                'node_modules',
                'vendor',
                'var',
                'web',
            ])
            ->in(__DIR__)
            ->append([
                __FILE__,
                __DIR__ . '/bin/console',
                __DIR__ . '/web/app.php',
                __DIR__ . '/web/app_dev.php',
                __DIR__ . '/web/index.php',
            ]),
    )
    ->setCacheFile('.php-cs-fixer.cache')
;
