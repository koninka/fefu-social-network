<?php

namespace Network\WebSocketBundle\Server;

use Network\WebSocketBundle\Application\Application;
use Wrench\BasicServer as BaseServer;

class Server extends BaseServer
{
    /**
     * @see Wrench.Server::registerApplication()
     */
    public function registerApplication($key, $application)
    {
        $this->log(sprintf(
                'Registering application: %s (%s)',
                $key,
                get_class($application)
            ), 'info');

        if ($application instanceof Application) {
            $application->setLogger($this->logger);
        } else {
            $this->log(
                'Application uses its own logging!',
                'warning'
            );
        }

        parent::registerApplication($key, $application);
    }

    /**
     * @see Wrench.Server::log()
     */
    public function log($message, $level = 'info')
    {
        $l = $this->logger;
        $l($message, $level);
    }
}
