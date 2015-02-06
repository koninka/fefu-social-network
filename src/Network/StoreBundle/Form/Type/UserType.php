<?php

namespace Network\StoreBundle\Form\Type;

use Network\StoreBundle\Form\Type\BaseType;

class UserType extends BaseType
{

    protected $entityClass = 'Network\StoreBundle\Entity\User';

    public function getName()
    {
        return 'network_storebundle_user';
    }

}
