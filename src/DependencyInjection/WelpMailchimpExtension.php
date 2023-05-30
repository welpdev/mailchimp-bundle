<?php

namespace Welp\MailchimpBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WelpMailchimpExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('welp_mailchimp.lists', $config['lists']);

        $container->setParameter('welp_mailchimp.list_provider', $config['list_provider']);
        $container->setParameter('welp_mailchimp.api_key', isset($config['api_key']) ? $config['api_key'] : null);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // create an alias for the chosen list provider service
        $alias = $config['list_provider'];
        $container->setAlias('welp_mailchimp.list_provider.current', $alias);

        // Load all the used subscriber providers in the factory
        $this->loadSubscriberProviders($container, $config['lists']);
    }

    public function getAlias(): string
    {
        return 'welp_mailchimp';
    }

    public function loadSubscriberProviders(ContainerBuilder $container, $lists)
    {
        $providerFactory = $container->getDefinition('welp_mailchimp.provider.factory');
        foreach ($lists as $list) {
            $providerKey = $list['subscriber_provider'];
            $providerFactory->addMethodCall('addProvider', [$providerKey, new Reference($providerKey)]);
        }
    }
}
