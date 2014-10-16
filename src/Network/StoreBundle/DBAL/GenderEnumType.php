<?php

namespace Network\StoreBundle\DBAL;

class GenderEnumType extends EnumType
{

    protected $name = 'genderEnumType';
    protected $values = [
        'male',
        'female',
    ];

}
