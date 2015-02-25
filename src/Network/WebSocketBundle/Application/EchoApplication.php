<?php

namespace Network\WebSocketBundle\Application;

class EchoApplication extends Application
{
    protected $clients = array();

    public function getName()
    {
        return 'echo';
    }

    public function onConnect($client)
    {
        $this->clients[] = $client;
        $client->send('Hey');
    }

    public function onDisconnect($client)
    {
        $key = array_search($client, $this->clients);
        if ($key) {
            unset($this->clients[$key]);
        }
    }

    public function onData($data, $client)
    {
        foreach ($this->clients as $sendTo) {
            $sendTo->send($data);
        }
    }
}
