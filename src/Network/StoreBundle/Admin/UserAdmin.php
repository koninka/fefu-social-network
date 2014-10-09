<?php

namespace Network\StoreBundle\Admin;

class UserAdmin extends VDolgahAdmin
{

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->configureFields([
            [
                'field' => 'login',
                'identifier' => true,
            ],
            [
                'field' => 'salt',
                'options' => [
                    'required' => false,
                    'read_only' => true
                ]
            ],
            [
                'field' => 'password',
            ]
        ]);
    }

    public function prePersist($object)
    {
        $this->rehash($object);
    }

    public function preUpdate($object)
    {
        $DM = $this->getConfigurationPool()->getContainer()->get('Doctrine')->getManager();
        $uow = $DM->getUnitOfWork();
        $originalEntityData = $uow->getOriginalEntityData($object);
        if ($originalEntityData['password'] != $object->getPassword()) {
            $this->rehash($object);
        }
    }

    private function rehash($object)
    {
        $salt = md5(time());
        $encoder = $this->getConfigurationPool()->getContainer()->get('security.encoder_factory')->getEncoder($object);
        $password = $encoder->encodePassword($object->getPassword(), $salt);
        $object->setPassword($password)->setSalt($salt);
    }
} 
