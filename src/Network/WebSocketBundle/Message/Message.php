<?php

namespace Network\WebSocketBundle\Message;

use Serializable;

class Message implements Serializable {

    const ACTION_FIELD = 'action';
    const DATA_FIELD = 'data';

    const ACTION = 'send';

    public  $data;
    public  $userId;

    public function __construct($userId, $data) {
        $this->data = $data;
        $this->userId = $userId;
    }

    public function serialize() {
        return serialize(
            [
                'user_id' => $this->userId,
                'data'    => $this->data,
            ]
        );
    }

    public function unserialize($d) {
        $d = unserialize($d);

        $this->$data = $d['data'];
        $this->$data = $d['user_id'];
    }

    public function toArray() {
        $m = [
            static::ACTION_FIELD => static::ACTION,
            static::DATA_FIELD => $this->data,
        ];
        return $m;
    }

    public function __toString() {
        return 'msg';
    }
}
