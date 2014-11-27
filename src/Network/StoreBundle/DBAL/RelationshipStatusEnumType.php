<?php

namespace Network\StoreBundle\DBAL;

class RelationshipStatusEnumType extends EnumType
{

    const FS_NONE = 'relationship_none';
    const FS_ACCEPTED = 'friendship_accepted';
    const FS_SUBSCRIBED_BY_ME = 'friendship_subscribed_by_me';
    const FS_SUBSCRIBED_BY_USER = 'friendship_subscribed_by_user';

    protected $name = 'relationshipStatusEnumType';
    protected $values = [
        self::FS_NONE,
        self::FS_ACCEPTED,
        self::FS_SUBSCRIBED_BY_ME,
        self::FS_SUBSCRIBED_BY_USER
    ];

}