<?php

namespace Network\StoreBundle\DBAL;

class TypeCommunityEnumType extends EnumType
{

    const C_OPEN = 'open';
    const C_CLOSED = 'closed';

    protected $name = 'typeCommunityEnumType';
    protected $values = [
        self::C_OPEN,
        self::C_CLOSED
    ];

}
