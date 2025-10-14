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
                        ->booleanNode('require_email_verification')
                            ->defaultFalse()
                            ->info('Require email verification for new users')
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
                ->arrayNode('registration')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                            ->info('Enable or disable user registration feature')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('login')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_method')
                            ->defaultValue('form_login')
                            ->info('Default login method: "form_login" or "login_link". For "login_link", make sure to enable it in the "login_link" section.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('login_link')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Enable or disable login with link feature')
                        ->end()
                        ->scalarNode('email_subject')
                            ->defaultValue('Your login link')
                            ->info('Subject of the email sent with the login link (text or translation key)')
                        ->end()
                        ->integerNode('limit_max_ask_by_minute')
                            ->defaultValue(3)
                            ->info('Maximum number of login link requests in one minute')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('request_password')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('email_subject')
                            ->defaultValue('Reset your password')
                            ->info('Subject of the email sent with the password reset link (text or translation key)')
                        ->end()
                        ->scalarNode('email_content')
                            ->defaultValue('Click on the button below to reset your password.')
                            ->info('Content of the email sent with the password reset link (text or translation key)')
                        ->end()
                        ->scalarNode('email_action_text')
                            ->defaultValue('Reset my password')
                            ->info('Text of the action button in the email sent with the password reset link (text or translation key)')
                        ->end()
                        ->integerNode('lifetime')
                            ->defaultValue(900)
                            ->info('Lifetime of the password reset token in seconds')
                        ->end()
                        ->scalarNode('redirect_route')
                            ->defaultValue('app_homepage')
                            ->info('Route to redirect after password reset.')
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder
    ): void {
        $container->import('../config/services.yaml');

        $this->registerParameters($config, $container);
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $securityConfig = $builder->getExtensionConfig('security');
        $lifetime = 600;
        if (
            \array_key_exists('firewalls', $securityConfig)
            && \array_key_exists('fastfony_identity', $securityConfig['firewalls'])
            && \array_key_exists('login_link', $securityConfig['firewalls']['fastfony_identity'])
            && \array_key_exists('lifetime', $securityConfig['firewalls']['fastfony_identity']['login_link'])
        ) {
            $lifetime = (int) $securityConfig['firewalls']['fastfony_identity']['login_link']['lifetime'];
        }

        $container->parameters()->set('fastfony_identity.login_link.lifetime', $lifetime);
    }

    private function registerParameters(
        array $config,
        ContainerConfigurator $container
    ): void {
        $parameters = [
            'fastfony_identity.user.class' => $config['user']['class'],
            'fastfony_identity.user.require_email_verification' => $config['user']['require_email_verification'],
            'fastfony_identity.role.class' => $config['role']['class'],
            'fastfony_identity.role.default_role' => $config['role']['default_role'],
            'fastfony_identity.group.class' => $config['group']['class'],
            'fastfony_identity.registration.enabled' => $config['registration']['enabled'],
            'fastfony_identity.login.default_method' => $config['login']['default_method'],
            'fastfony_identity.login_link.enabled' => $config['login_link']['enabled'],
            'fastfony_identity.login_link.email_subject' => $config['login_link']['email_subject'],
            'fastfony_identity.login_link.limit_max_ask_by_minute' => $config['login_link']['limit_max_ask_by_minute'],
            'fastfony_identity.request_password.email_subject' => $config['request_password']['email_subject'],
            'fastfony_identity.request_password.email_content' => $config['request_password']['email_content'],
            'fastfony_identity.request_password.email_action_text' => $config['request_password']['email_action_text'],
            'fastfony_identity.request_password.lifetime' => $config['request_password']['lifetime'],
            'fastfony_identity.request_password.redirect_route' => $config['request_password']['redirect_route'],
        ];

        foreach ($parameters as $key => $value) {
            $container->parameters()->set($key, $value);
        }
    }
}
