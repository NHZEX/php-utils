<?php

$finder = PhpCsFixer\Finder::create()->in('src');
$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PSR12'                     => true,
        '@PHP71Migration'            => true,
        // Converts backtick operators to `shell_exec` calls.
        'backtick_to_shell_exec'     => true,
        // Binary operators should be surrounded by space as configured.
        'binary_operator_spaces'     => [
            'default'   => 'single_space',
            'operators' => [
                '=>' => null, // at least one
                '='  => null, // at least one
            ],
        ],
        // Replace core functions calls returning constants with the constants.
        'function_to_constant'       => true,
        // Imports or fully qualifies global classes/functions/constants.
        'global_namespace_import'    => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        // Add leading `\` before function invocation to speed up resolving.
        'native_function_invocation' => ['include' => ['@internal'], 'scope' => 'namespaced', 'strict' => true],
        // Array index should always be written by using square braces.
        'normalize_index_brace'      => true,
        // Operators - when multiline - must always be at the beginning or at the end of the line.
        'operator_linebreak'         => ['only_booleans' => true, 'position' => 'beginning'],
        // Replace all `<>` with `!=`.
        'standardize_not_equals'     => true,
        // Unary operators should be placed adjacent to their operands.
        'unary_operator_spaces'      => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
