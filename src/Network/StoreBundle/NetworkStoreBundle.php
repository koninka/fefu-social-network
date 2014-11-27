<?php

namespace Network\StoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use \Doctrine\DBAL\Types\Type;

class NetworkStoreBundle extends Bundle
{

    public function __construct()
    {
        //TODO its not fine
        if (!Type::hasType('genderEnumType')) {
            Type::addType('genderEnumType', 'Network\StoreBundle\DBAL\GenderEnumType');
        }
        if (!Type::hasType('roleEnumType')) {
            Type::addType('roleEnumType', 'Network\StoreBundle\DBAL\RoleEnumType');
        }
        if (!Type::hasType('relationshipStatusEnumType')) {
            Type::addType('relationshipStatusEnumType', 'Network\StoreBundle\DBAL\RelationshipStatusEnumType');
        }
    }

}
