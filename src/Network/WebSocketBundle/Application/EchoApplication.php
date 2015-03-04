<?php

namespace Network\WebSocketBundle\Application;


use Symfony\Component\Config\Definition\Exception\Exception;
use Network\WebSocketBundle\Message\Message;

class EchoApplication extends Application
{
    const MSG_CONTAINER = 'data';

    protected $clients = [];
    protected $users = [];

    const CLIENT_KEY = 'client';
    const USER_ID_KEY = 'user_id';

    const ACTION_KEY = 'action';
    const DATA_KEY = 'data';

    public function getName()
    {
        return 'echo';
    }

    private function validateData($data)
    {

        return (is_array($data) && array_key_exists(self::ACTION_KEY, $data) && array_key_exists(
                self::DATA_KEY,
                $data
            ));
    }

    public function onConnect($client)
    {
        $this->log('Client connected ' . $client->getSocket()->getIp() . ':' . $client->getSocket()->getPort());
    }

    public function onDisconnect($client)
    {
        $clientId = $client->getId();
        if (!array_key_exists($clientId, $this->clients)) {
            return;
        }
        $userId = $this->clients[$clientId][self::USER_ID_KEY];
        unset($this->clients[$clientId]);
        if (!array_key_exists($userId, $this->users)) {
            return;
        }
        //loop connected sockets with this userId
        for ($i = 0; $i < count($this->users[$userId]); $i++) {
            //delete needed one
            if (array_key_exists($i, $this->users[$userId]) && $this->users[$userId][$i][self::CLIENT_KEY]->getId() == $clientId) {
                unset($this->users[$userId][$i]);
                if (count($this->users[$userId]) == 0) {
                    unset($this->users[$userId]);
                }
                break;
            }
        }
    }

    public function onUpdate()
    {
        $mem = new \Jamm\Memory\RedisObject('messages');

        $messages = $mem->read(self::MSG_CONTAINER);

        if (!empty($messages)) {
            $newMessages = [];
            foreach ($messages as $msg) {
                if (!array_key_exists($msg->userId, $this->users)) {
                    $newMessages[] = $msg;
                    continue;
                }

                foreach ($this->users[$msg->userId] as $user) {
                    if ($user[self::CLIENT_KEY]->getSocket()->isConnected()) {
                        $user[self::CLIENT_KEY]->send(json_encode($msg->toArray()));
                    }
                }
            }
            $mem->del(self::MSG_CONTAINER);
            $mem->save(self::MSG_CONTAINER, $newMessages);
        }
//
//        $bid = 5557;
//
//        $status = @shmop_open($bid, "w", 0, 0);
//
//        if ($status) {
//            $shmid    = shmop_open($bid, "a", 0, 0);
//            $size     = shmop_size($shmid);
//            $data     = shmop_read($shmid, 0, $size);
//            $messages = unserialize($data);
//            $this->log('Messages readed');
//
//
//            foreach ($messages as $userId => $userMessages) {
//                if (!array_key_exists($userId, $this->users)) {
//                    continue;
//                }
//                foreach ($userMessages as $msg) {
//                    $this->users[$userId][self::CLIENT_KEY]->send(json_encode($msg));
//                }
//
//                unset($messages[$userId]);
//            }
//
//            shmop_close($shmid);
//
//            $data = serialize($messages);
//            $size = mb_strlen($data, 'UTF-8');
//
//            $shmid = shmop_open($bid, "w", 0777, $size);
//            shmop_write($shmid, $data, 0);
//            shmop_close($shmid);
//
//        }
    }

    public function onData($data, $client)
    {
        $data = json_decode($data, true);
        if (!$this->validateData($data)) {
            exit;
        }
        $clientId = $client->getId();
        if (array_key_exists($clientId, $this->clients)) {
            //already authed
            //handle other messages here
            return;
        }
        if ($data[self::ACTION_KEY] === 'auth') {
            $users = $this->em->getRepository('NetworkStoreBundle:User')->findBy(
                ['webSocketAuthKey' => $data[self::DATA_KEY]]
            );
                $user  = null;
            if (count($users) > 0) {
                $user = $users[0];
            }
            if ($user) {
                $this->users[$user->getId()][] = [
                    self::CLIENT_KEY  => $client,
                    self::USER_ID_KEY => $user->getId()
                ];
                $this->clients[$clientId]    = [
                    self::CLIENT_KEY  => $client,
                    self::USER_ID_KEY => $user->getId()
                ];

                $this->log('User with id ' . $user->getId() . ' authed');

//                    $m = [
//                        Message::ACTION_FIELD => Message::ACTION,
//                        Message::TEXT_FIELD => 'You are connected and your id is ' . $user->getId(),
//                        Message::TYPE_FIELD => Message::TYPE_NORMAL
//                    ];
//                    $client->send(json_encode($m));
                }
        }
    }
}
