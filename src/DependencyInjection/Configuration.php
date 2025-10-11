<?php

namespace Fastfony\IdentityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('fastfony_identity');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('user')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->defaultValue('App\\Entity\\User')
                            ->info('The User entity class')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('role')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->defaultValue('App\\Entity\\Role')
                            ->info('The Role entity class')
                        ->end()
                        ->scalarNode('default_role')
                            ->defaultValue('ROLE_USER')
                            ->info('Default role for new users')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('group')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->defaultValue('App\\Entity\\Group')
                            ->info('The Group entity class')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
