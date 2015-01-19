<?php

namespace Network\StoreBundle\DBAL;

class ThreadEnumType extends EnumType
{

    const T_DIALOG = 'thread_type_dialog';
    const T_CONFERENCE = 'thread_type_conference';
    protected $name = 'threadEnumType';
    protected $values = [
        self::T_DIALOG,
        self::T_CONFERENCE,
    ];

}
