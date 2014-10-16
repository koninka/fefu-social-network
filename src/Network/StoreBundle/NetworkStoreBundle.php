<?php

namespace Network\StoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use \Doctrine\DBAL\Types\Type;

class NetworkStoreBundle extends Bundle
{

    public function __construct()
    {
        if (!Type::hasType('genderEnumType')) {
            Type::addType('genderEnumType', 'Network\StoreBundle\DBAL\GenderEnumType');
        }
    }

}
