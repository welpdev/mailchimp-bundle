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
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()

                            // tags
                            ->arrayNode('merge_tags')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('tag')
                                            ->isRequired()
                                        ->end()
                                        ->scalarNode('name')
                                            ->isRequired()
                                        ->end()

                                        // tag options
                                        ->arrayNode('options')
                                            ->children()
                                                ->scalarNode('field_type')
                                                    ->defaultValue('text')
                                                ->end()
                                                ->booleanNode('req')
                                                    ->defaultValue(false)
                                                ->end()
                                                ->booleanNode('public')
                                                    ->defaultValue(true)
                                                ->end()
                                                ->booleanNode('show')
                                                    ->defaultValue(true)
                                                ->end()
                                                ->integerNode('order')
                                                ->end()
                                                ->scalarNode('default_value')
                                                ->end()
                                                ->scalarNode('helptext')
                                                ->end()
                                                ->arrayNode('choices')
                                                    ->prototype('scalar')->end()
                                                    ->defaultValue([])
                                                ->end()
                                                ->scalarNode('dateformat')
                                                ->end()
                                                ->scalarNode('phoneformat')
                                                ->end()
                                                ->scalarNode('defaultcountry')
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()

                            ->scalarNode('mc_language')
                                ->defaultNull()
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
