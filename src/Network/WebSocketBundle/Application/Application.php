<?php

namespace Network\WebSocketBundle\Application;

use Wrench\Application\Application as BaseApplication;

use \Closure;

abstract class Application extends BaseApplication
{
    /**
     * Active clients
     *
     * @var array
     */
    protected $clients = array();

    /**
     * Closure logger
     *
     * @var Closure
     */
    protected $logger;

    public function __construct()
    {
        $this->logger = function($message, $level = 'info') {
            echo $level . ': ' . $message . "\n";
        };
    }

    /**
     * Sets the logger
     *
     * @param Closure $logger
     */
    public function setLogger(Closure $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs a message to the server log
     *
     * @param string $message
     * @param string $level
     */
    public function log($message, $level = 'info')
    {
        $message = $this->getName() . ': ' . $message;

        $log = $this->logger;
        $log($message, $level);
    }

    public function onConnect($client)
    {
        $this->clients[] = $client;
    }

    public function onDisconnect($client)
    {
        $key = array_search($client, $this->clients);
        if ($key) {
            unset($this->clients[$key]);
        }
    }

    /**
     * Sends the data to all connected clients
     *
     * @param mixed $data
     * @return array
     */
    public function sendToAll($data)
    {
        $collected = array();
        foreach ($this->clients as $client) {
            $collected[] = $client->send($data);
        }
        return $collected;
    }
}
