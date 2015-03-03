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

    const MESSAGE_TEXT_FIELD = 'text';
    const MESSAGE_TYPE_FIELD = 'type';

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
        $id = $client->getId();
        unset($this->clients[$id]);
    }

    public function onUpdate()
    {

        $mem = new \Jamm\Memory\RedisObject('messages');

        $messages = $mem->read(EchoApplication::MSG_CONTAINER);

        if (!empty($messages)) {
            $newMessages = [];
            foreach ($messages as $msg) {
                if (!array_key_exists($msg->userId, $this->users)) {
                    $newMessages[] = $msg;
                    continue;
                }

                $m = [
                    self::MESSAGE_TEXT_FIELD => $msg->text,
                    self::MESSAGE_TYPE_FIELD => $msg->type
                ];

                $this->users[$msg->userId][self::CLIENT_KEY]->send(json_encode($m));
            }
            $mem->del('data');
            $mem->save('data', $newMessages);
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
        if (!array_key_exists($clientId, $this->clients)) {
            if ($data[self::ACTION_KEY] === 'auth') {
                $users = $this->em->getRepository('NetworkStoreBundle:User')->findBy(
                    ['webSocketAuthKey' => $data[self::DATA_KEY]]
                );
                $user  = null;
                if (count($users) > 0) {
                    $user = $users[0];
                }
                if ($user) {
                    $this->clients[$clientId]    = [
                        self::CLIENT_KEY  => $client,
                        self::USER_ID_KEY => $user->getId()
                    ];
                    $this->users[$user->getId()] = [
                        self::CLIENT_KEY  => $client,
                        self::USER_ID_KEY => $user->getId()
                    ];

                    $this->log('User with id ' . $user->getId() . ' authed');

                    $m = [
                        self::MESSAGE_TEXT_FIELD => 'You are connected and your id is ' . $user->getId(),
                        self::MESSAGE_TYPE_FIELD => Message::TYPE_NORMAL
                    ];
                    $client->send(json_encode($m));
                }
            }
        } else {

        }
    }
}
