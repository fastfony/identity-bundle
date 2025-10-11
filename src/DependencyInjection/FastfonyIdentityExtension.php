<?php

namespace Fastfony\IdentityBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class FastfonyIdentityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Store configuration in container parameters
        $container->setParameter('fastfony_identity.user.class', $config['user']['class']);
        $container->setParameter('fastfony_identity.role.class', $config['role']['class']);
        $container->setParameter('fastfony_identity.role.default_role', $config['role']['default_role']);
        $container->setParameter('fastfony_identity.group.class', $config['group']['class']);

        // Load services
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }
}
