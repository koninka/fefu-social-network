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
        if (!Type::hasType('roleCommunityEnumType')) {
            Type::addType('roleCommunityEnumType', 'Network\StoreBundle\DBAL\RoleCommunityEnumType');
        }
        if (!Type::hasType('viewCommunityEnumType')) {
            Type::addType('viewCommunityEnumType', 'Network\StoreBundle\DBAL\ViewCommunityEnumType');
        }
        if (!Type::hasType('typeCommunityEnumType')) {
            Type::addType('typeCommunityEnumType', 'Network\StoreBundle\DBAL\TypeCommunityEnumType');
        }
    }

}
