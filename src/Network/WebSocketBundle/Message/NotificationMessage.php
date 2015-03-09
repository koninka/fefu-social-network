<?php

namespace Network\WebSocketBundle\Message;

use Network\WebSocketBundle\Message\Message;

class NotificationMessage extends Message {

    const TYPE_SUCCESS = 'green';
    const TYPE_FAIL = 'red';
    const TYPE_NORMAL = 'black';

    const ACTION = 'notify';

    const DATA_FIELD = 'text';
    const TYPE_FIELD = 'type';

    public $userId;
    public $type;

    public function __construct($userId, $text, $type) {
        $this->userId = $userId;
        $this->data = $text;
        $this->type = $type;
    }

    public function serialize() {
        return serialize(
            [
                'user_id' => $this->userId,
                'text'    => $this->data,
                'type'    => $this->type
            ]
        );
    }


    public function unserialize($d) {
        $d = unserialize($d);

        $this->userId   = $d['user_id'];
        $this->data     = $d['text'];
        $this->type     = $d['type'];
    }

    public function toArray() {
        $m = [
            static::ACTION_FIELD => static::ACTION,
            static::DATA_FIELD => $this->data,
            static::TYPE_FIELD => $this->type
        ];
        return $m;
    }

    public function __toString() {
        return 'msg.' . $this->userId;
    }
}
