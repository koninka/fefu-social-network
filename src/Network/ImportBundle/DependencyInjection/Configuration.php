<?php

namespace Network\ImportBundle\DependencyInjection;

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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('network_import')
            ->children()
                ->scalarNode('config_import_path')->end()
                ->arrayNode('endpoints')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->enumNode('owner')
                            ->values(array('instagram', 'facebook', 'vkontakte'))
                        ->end()
                        ->scalarNode('url')->end()
                        ->enumNode('method')
                            ->values(array('POST', 'GET', 'PUT', 'DELETE'))
                        ->end()
                        ->arrayNode('paths')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('json_root')->end()
                        ->arrayNode('config')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('schedule_params')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('headers')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
