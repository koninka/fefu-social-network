<?php

namespace Network\WebSocketBundle\Message;

use Network\WebSocketBundle\Message\Message;

class ImMessage extends Message {

    const ACTION = 'deliver';

    const THREAD_ID_FIELD = 'thread_id';
    const DATA_FIELD = 'post';


    public $threadId;
    public $post;

    public function __construct($threadId, $userId, $data) {
        $this->threadId = $threadId;
        $this->userId = $userId;
        $this->post = $data;
    }

    public function serialize() {
        return serialize(
            [
                'thread_id' => $this->threadId,
                'user_id'   => $this->userId,
                'post'      => $this->post,
            ]
        );
    }

    public function unserialize($d) {
        $d = unserialize($d);

        $this->threadId = $d['thread_id'];
        $this->userId   = $d['user_id'];
        $this->post     = $d['post'];
    }

    public function toArray() {
        $m = [
            static::ACTION_FIELD  => static::ACTION,
            static::DATA_FIELD    => $this->post,
            self::THREAD_ID_FIELD => $this->threadId,
        ];
        return $m;
    }

}
