<?php

namespace Fastfony\IdentityBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class FastfonyIdentityBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode();

        $rootNode
            ->children()
                ->arrayNode('user')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('class')
                        ->defaultValue('Fastfony\\IdentityBundle\\Entity\\Identity\\User')
                        ->info('The User entity class')
                    ->end()
                ->end()
            ->end()
            ->arrayNode('role')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('class')
                        ->defaultValue('Fastfony\\IdentityBundle\\Entity\\Identity\\Role')
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
                        ->defaultValue('Fastfony\\IdentityBundle\\Entity\\Identity\\Group')
                        ->info('The Group entity class')
                    ->end()
                ->end()
            ->end()
        ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $container->parameters()->set('fastfony_identity.user.class', $config['user']['class']);
        $container->parameters()->set('fastfony_identity.role.class', $config['role']['class']);
        $container->parameters()->set('fastfony_identity.role.default_role', $config['role']['default_role']);
        $container->parameters()->set('fastfony_identity.group.class', $config['group']['class']);
    }
}
