<?php

namespace Network\UserBundle\Doctrine;
use FOS\UserBundle\Doctrine\UserManager as BaseController;
use Network\StoreBundle\Entity\ContactInfo;
use Network\StoreBundle\Entity\Blacklist;

class UserManager extends BaseController
{
    public function createUser()
    {
        $class = $this->getClass();
        $user = new $class;
        $user->setContactInfo(new ContactInfo());
        $user->setBlacklist(new Blacklist());

        return $user;
    }
}

