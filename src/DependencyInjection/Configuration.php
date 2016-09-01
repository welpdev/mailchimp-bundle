<?php

namespace Welp\MailchimpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('welp_mailchimp');

        $rootNode
            ->children()
                ->scalarNode('api_key')->end()
                // lists
                ->arrayNode('lists')
                    ->useAttributeAsKey('listId')
                    ->prototype('array')
                        ->children()
                            // merge_fields
                            ->arrayNode('merge_fields')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('tag')
                                            ->isRequired()
                                        ->end()
                                        ->scalarNode('name')
                                            ->isRequired()
                                        ->end()
                                        ->scalarNode('type')
                                            ->isRequired()
                                        ->end()
                                        ->booleanNode('required')
                                        ->end()
                                        ->scalarNode('default_value')
                                        ->end()
                                        ->booleanNode('public')
                                        ->end()
                                        ->integerNode('display_order')
                                        ->end()
                                        // tag options
                                        ->arrayNode('options')
                                            ->children()
                                                ->integerNode('default_country')
                                                ->end()
                                                ->scalarNode('phone_format')
                                                ->end()
                                                ->scalarNode('date_format')
                                                ->end()
                                                ->arrayNode('choices')
                                                ->end()
                                                ->integerNode('size')
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->scalarNode('help_text')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('subscriber_provider')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
