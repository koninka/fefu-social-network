<?php

namespace Network\StoreBundle\DBAL;

class ViewCommunityEnumType extends EnumType
{

    const SC_GROUP = 'group';
    const SC_ACTION = 'action';

    protected $name = 'viewCommunityEnumType';
    protected $values = [
        self::SC_GROUP ,
        self::SC_ACTION
    ];

}
