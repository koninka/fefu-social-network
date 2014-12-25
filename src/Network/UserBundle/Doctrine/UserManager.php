<?php

namespace Network\UserBundle\Doctrine;
use FOS\UserBundle\Doctrine\UserManager as BaseController;
use Network\StoreBundle\Entity\ContactInfo;

class UserManager extends BaseController
{
    public function createUser()
    {
        $class = $this->getClass();
        $user = new $class;
        $user->setContactInfo(new ContactInfo());

        return $user;
    }
}

