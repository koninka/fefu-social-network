<?php

namespace Network\StoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends BaseType
{

    protected $entityClass = 'Network\StoreBundle\Entity\User';

    public function getName()
    {
        return 'network_storebundle_user';
    }

} 
