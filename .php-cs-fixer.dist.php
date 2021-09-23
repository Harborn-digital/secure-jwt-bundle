<?php
$config = new PhpCsFixer\Config();
$config
    ->setRules([
        '@Symfony'               => true,
        'ordered_imports'        => true,
        'yoda_style'             => true,
        'phpdoc_order'           => true,
        'array_syntax'           => [
            'syntax' => 'short',
        ],
        'binary_operator_spaces' =>
            ['operators' => [
                '=>' => 'align_single_space_minimal',
                '=' => 'align_single_space_minimal'
            ]],
        'header_comment' => [
            'header' => <<<EOH
This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
Copyright (c) 2020-2021 Connect Holland.
EOH
                ,
            ]
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()->in([
            __DIR__.'/src',
            __DIR__.'/tests'
        ]
    ));

return $config;