<?php

namespace Network\StoreBundle\DBAL;

class FriendshipStatusEnumType extends EnumType
{

    const FS_REQUESTED = 'requested';
    const FS_ACCEPTED = 'accepted';

    protected $name = 'friendshipStatusEnumType';
    protected $values = [
        self::FS_REQUESTED,
        self::FS_ACCEPTED,
    ];

}