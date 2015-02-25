<?php

namespace Network\WebSocketBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ApplicationCompilerPass implements CompilerPassInterface
{
    /**
     * @see Symfony\Component\DependencyInjection\Compiler.CompilerPassInterface::process()
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('network_web_socket.server_manager')) {
            $definition = $container->getDefinition('network_web_socket.server_manager');

            foreach ($container->findTaggedServiceIds('network_web_socket.application') as $id => $attributes) {
                if (!isset($attributes[0]['key']) || !$attributes[0]['key']) {
                    throw new \Exception('You must give network_web_socket.application tags a key attribute');
                }
                $definition->addMethodCall('addApplication', array($attributes[0]['key'], new Reference($id)));
            }
        }
    }
}
