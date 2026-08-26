<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->in(__DIR__ . '/migrations')
    ->name('*.php')
    ->notName('Kernel.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        // ── PSR-12 Base ──────────────────────────────────────────────────
        '@PSR12'                            => true,

        // ── PHP 8.x Modern Syntax ────────────────────────────────────────
        '@PHP80Migration'                   => true,
        '@PHP80Migration:risky'             => true,

        // ── Symfony Coding Standards ─────────────────────────────────────
        '@Symfony'                          => true,
        '@Symfony:risky'                    => true,

        // ── Imports & Namespace ──────────────────────────────────────────
        'ordered_imports'                   => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'                 => true,
        'single_import_per_statement'       => true,
        'global_namespace_import'           => [
            'import_classes'    => false,
            'import_constants'  => false,
            'import_functions'  => false,
        ],

        // ── Arrays ───────────────────────────────────────────────────────
        'array_syntax'                      => ['syntax' => 'short'],
        'trailing_comma_in_multiline'       => ['elements' => ['arrays', 'parameters', 'arguments']],
        'normalize_index_brace'             => true,
        'no_whitespace_before_comma_in_array' => true,

        // ── Strings ──────────────────────────────────────────────────────
        'single_quote'                      => true,
        'no_binary_string'                  => true,
        'explicit_string_variable'          => true,

        // ── Control Structures ───────────────────────────────────────────
        'yoda_style'                        => false,
        'no_superfluous_elseif'             => true,
        'no_useless_else'                   => true,
        'simplified_if_return'              => true,

        // ── Functions & Methods ──────────────────────────────────────────
        'void_return'                       => true,
        'return_type_declaration'           => ['space_before' => 'none'],
        'phpdoc_to_comment'                 => false,
        'no_unreachable_default_argument_value' => true,
        'nullable_type_declaration_for_default_null_value' => true,

        // ── PhpDoc ───────────────────────────────────────────────────────
        'phpdoc_align'                      => ['align' => 'left'],
        'phpdoc_order'                      => true,
        'phpdoc_separation'                 => true,
        'phpdoc_trim'                       => true,
        'phpdoc_scalar'                     => true,
        'phpdoc_no_empty_return'            => true,
        'phpdoc_var_without_name'           => true,

        // ── Casting ──────────────────────────────────────────────────────
        'modernize_types_casting'           => true,
        'no_short_bool_cast'                => true,

        // ── Class Structure ──────────────────────────────────────────────
        'class_attributes_separation'       => [
            'elements' => [
                'const'         => 'one',
                'method'        => 'one',
                'property'      => 'none',
                'trait_import'  => 'none',
            ],
        ],
        'ordered_class_elements'            => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        'no_blank_lines_after_class_opening' => true,
        'self_accessor'                     => true,

        // ── Whitespace & Formatting ──────────────────────────────────────
        'blank_line_before_statement'       => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'concat_space'                      => ['spacing' => 'one'],
        'binary_operator_spaces'            => ['default' => 'single_space'],
        'unary_operator_spaces'             => true,
        'not_operator_with_successor_space' => false,
        'no_extra_blank_lines'              => [
            'tokens' => [
                'curly_brace_block', 'extra', 'parenthesis_brace_block',
                'square_brace_block', 'throw', 'use',
            ],
        ],

        // ── Strict ───────────────────────────────────────────────────────
        'strict_param'                      => true,
        'declare_strict_types'              => true,
        'is_null'                           => true,
        'strict_comparison'                 => true,
    ])
    ->setFinder($finder)
    ->setLineEnding("\n");
