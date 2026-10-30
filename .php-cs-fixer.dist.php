<?php


use PhpCsFixer\Finder;
use PhpCsFixer\Config;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new Finder())
    ->in(__DIR__)
    ->exclude([
        '.git/',
        'vendor/',
    ])
;

return (new Config())
    ->setUnsupportedPhpVersionAllowed(true) // allow upcoming PHP versions
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setCacheFile(sys_get_temp_dir() . '/php-cs-fixer.glpi-inventory-plugin.cache')
    ->setRules([
        '@PER-CS3.0' => true,
        '@PHP84Migration' => true,
        'fully_qualified_strict_types' => ['import_symbols' => true],
        'ordered_imports' => ['imports_order' => ['class', 'const', 'function']],
        'no_unused_imports' => true,
        'heredoc_indentation' => false, // This rule is mandatory due to a bug in `xgettext`, see https://savannah.gnu.org/bugs/?func=detailitem&item_id=62158
        'new_expression_parentheses' => false, // breaks compatibility with PHP < 8.4
        'phpdoc_scalar' => true, // Normalize scalar types identifiers in PHPDoc
        'phpdoc_types' => true, // Fixes types case in PHPDoc
    ])
    ->setFinder($finder)
;
