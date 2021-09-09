<?php


namespace ConnectHolland\SecureJWTBundle\DependencyInjection;


    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const CONFIG_ROOT_KEY = 'connect_holland_secure_jwt';


    public function getConfigTreeBuilder(): TreeBuilder
    {

        $treeBuilder = new TreeBuilder(self::CONFIG_ROOT_KEY);
        $rootNode    = $treeBuilder->getRootNode();


        $rootNode
            ->addDefaultsIfNotSet()
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
