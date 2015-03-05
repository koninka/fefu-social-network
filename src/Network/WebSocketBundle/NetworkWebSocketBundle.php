<?php

namespace Network\WebSocketBundle;

use Network\WebSocketBundle\DependencyInjection\ApplicationCompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetworkWebSocketBundle extends Bundle
{
    /**
     * Format for server service IDs
     *
     * @var string
     */
    const SERVICE_FORMAT = 'network_web_socket.server_%s';

    /**
     * @see Symfony\Component\HttpKernel\Bundle.Bundle::build()
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ApplicationCompilerPass());
    }

    /**
     * Gets the default service id for a given service name
     *
     * @param string $name
     * @return string
     */
    public static function getServerServiceId($name)
    {
        return sprintf(self::SERVICE_FORMAT, $name);
    }
}
