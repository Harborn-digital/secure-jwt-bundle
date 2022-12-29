<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace Harborn\SecureJWTBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const CONFIG_ROOT_KEY = 'harborn_digital_secure_jwt';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::CONFIG_ROOT_KEY);
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('is_remembered')
                    ->defaultFalse()
                ->end()
                ->integerNode('expiry_days')
                    ->min(0)
                    ->defaultValue(30)
                ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
