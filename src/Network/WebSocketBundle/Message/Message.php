<?php

namespace Network\WebSocketBundle\Message;

use Serializable;

class Message implements Serializable {

    const TYPE_SUCCESS = 'green';
    const TYPE_FAIL = 'red';
    const TYPE_NORMAL = 'black';

    public $userId;
    public $text;
    public $type;

    public function __construct($userId, $text, $type) {
        $this->userId = $userId;
        $this->text = $text;
        $this->type = $type;
    }

    public function serialize() {
        return serialize(
            [
                'user_id' => $this->userId,
                'text' => $this->text,
                'type' => $this->type
            ]
        );
    }

    public function unserialize($data) {
        $data = unserialize($data);

        $this->userId = $data['user_id'];
        $this->text = $data['text'];
        $this->type = $data['type'];
    }

    public function __toString() {
        return 'msg.' . $this->userId;
    }

}
