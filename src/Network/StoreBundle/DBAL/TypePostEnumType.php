<?php

namespace Network\StoreBundle\DBAL;

class TypePostEnumType extends EnumType
{
    const TP_TEXT = 'text';
    const TP_POLL = 'poll';
    protected $name = 'typePostEnumType';
    protected $values = [
        'text',
        'poll',
    ];

}
