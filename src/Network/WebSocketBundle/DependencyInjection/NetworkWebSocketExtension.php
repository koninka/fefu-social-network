<?php

namespace Network\WebSocketBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NetworkWebSocketExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->addServers($config, $container);
    }

    /**
     * Adds server configurations
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function addServers(array $config, ContainerBuilder $container)
    {
        $manager = $container->getDefinition('network_web_socket.server_manager');
        $manager->addMethodCall('setConfiguration', array($config['servers']));
    }

    /**
     * @see Symfony\Component\HttpKernel\DependencyInjection.Extension::getXsdValidationBasePath()
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__ . '/../Resources/config/';
    }


}
