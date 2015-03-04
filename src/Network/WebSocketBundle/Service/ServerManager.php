<?php

namespace Network\WebSocketBundle\Service;

use \InvalidArgumentException;
use Wrench\Protocol\Protocol;
use Wrench\Server;
use Wrench\Connection;
use \Closure;
use Monolog\Logger;
use Network\WebSocketBundle\Message\Message;
use Network\WebSocketBundle\Application\EchoApplication;

class ServerManager
{
    private $em;

    /**
     * @var Closure
     */
    protected $logger;

    /**
     * @var array<string => Server>
     */
    protected $servers = array();

    /**
     * @var array<Application>
     */
    protected $applications = array();

    /**
     * @var array<string => array>
     */
    protected $configuration;

    protected $notifyApp;

    /**
     * Constructor
     */
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em     = $em;
        $this->logger = function ($message, $level = 'info') {
            echo $level . ': ' . $message . "\n";
        };
    }

    public function addApplication($key, $application)
    {
        $this->applications[$key] = $application;
    }

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Gets a server by name
     *
     * @param string $name
     * @return Server
     */
    public function getServer($name)
    {
        if (!isset($this->servers[$name])) {
            return $this->createServer($name);
        }

        return $this->servers[$name];
    }

    /**
     * Creates a server
     *
     * @param string $name
     * @throws InvalidArgumentException
     * @return Server
     */
    public function createServer($name)
    {
        if (!isset($this->configuration[$name])) {
            throw new InvalidArgumentException('Invalid server name');
        }

        $config = $this->configuration[$name];

        $server = new $config['class'](
            $config['listen'],
            array(
                'logger'               => $this->logger,
                'allowed_origins'      => $config['allow_origin'],
                'check_origin'         => $config['check_origin'],
                'rate_limiter_options' => [
                    'connections'         => $config['max_clients'],
                    'connections_per_ip'  => $config['max_connections_per_ip'],
                    'requests_per_minute' => $config['max_requests_per_minute']
                ]
            )
        );

        foreach ($config['applications'] as $key) {
            if (!isset($this->applications[$key])) {
                throw new \RuntimeException('Invalid server config: application ' . $key . ' not found');
            }
            $server->registerApplication($key, $this->applications[$key]);
            $this->applications[$key]->setEm($this->em);
        }

        return (($this->servers[$name] = $server));
    }

    /**
     * @param \Closure|Logger $logger
     * @return void
     */
    public function setLogger($logger)
    {
        if ($logger instanceof Logger) {
            $this->logger = function ($message, $level) use ($logger) {
                switch ($level) {
                    case 'info':
                        $logger->info($message);

                        return;
                    case 'warn':
                    default:
                        $logger->warn($message);

                        return;
                }
            };
        } else {
            $this->logger = $logger;
        }
    }

    public function sendNotifyMessage(Message $msg)
    {


        $mem = new \Jamm\Memory\RedisObject('messages');

        $mem->increment(EchoApplication::MSG_CONTAINER, [$msg]);

//        $bid = 5557;
//        $mode = "w";
//        $status = @shmop_open($bid, "a", 0, 0);
//        if ($status) {
//            $shmid    = shmop_open($bid, "a", 0, 0);
//            $size     = shmop_size($shmid);
//            $data     = shmop_read($shmid, 0, $size);
//            $messages = unserialize($data);
////            shmop_delete($shmid);
//            shmop_close($shmid);
//        } else {
//            $messages = [];
//            $mode = "c";
//        }
//
//        $messages[$userId][] = $msg;
//
//        $data = serialize($messages);
//
//        $size = mb_strlen($data, 'UTF-8');
//
//        $shmid = shmop_open($bid, $mode, 0777, $size);
//        shmop_write($shmid, $data, 0);
//        shmop_close($shmid);


    }
}
