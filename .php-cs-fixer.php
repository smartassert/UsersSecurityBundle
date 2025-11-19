<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/Security')
    ->in(__DIR__ . '/Tests');

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PER-CS' => true,
    '@PhpCsFixer' => true,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'php_unit_internal_class' => false,
    'php_unit_test_class_requires_covers' => false,
    'declare_strict_types' => true,
])->setFinder($finder);
