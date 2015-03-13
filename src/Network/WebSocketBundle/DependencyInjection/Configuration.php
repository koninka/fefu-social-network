<?php

namespace Network\WebSocketBundle\DependencyInjection;

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
        $rootNode    = $treeBuilder->root('network_web_socket');

        $this->addServerSection($rootNode);

        return $treeBuilder;
    }

    protected function addServerSection($rootNode)
    {
        $rootNode
            ->children()
            ->arrayNode('servers')
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('class')->defaultValue('Network\WebSocketBundle\Server\Server')->end()
            ->scalarNode('listen')->defaultValue('ws://localhost')->end()
            ->booleanNode('ssl')->defaultValue(false)->end()
            ->scalarNode('max_clients')->defaultValue(30)->end()
            ->scalarNode('max_connections_per_ip')->defaultValue(5)->end()
            ->scalarNode('max_requests_per_minute')->defaultValue(50)->end()
            ->booleanNode('check_origin')->defaultValue(true)->end()
            ->arrayNode('allow_origin')
            ->defaultValue(array('localhost'))
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('applications')
            ->requiresAtLeastOneElement()
            ->isRequired()
            ->defaultValue(array('echo'))
            ->prototype('scalar')->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();
    }
}
