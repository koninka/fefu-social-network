<?php

namespace Network\StoreBundle\DBAL;

class RoleEnumType extends EnumType
{

    protected $name = 'roleEnumType';
    protected $values = [
        'ROLE_USER',
        'ROLE_ADMIN',
    ];

}
