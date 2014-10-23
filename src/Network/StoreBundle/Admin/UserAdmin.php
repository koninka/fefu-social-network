<?php

namespace Network\StoreBundle\Admin;

class UserAdmin extends VDolgahAdmin
{

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->configureFields([
            [
                parent::FIELD_KEY => 'username',
                parent::IDENTIFIER_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'salt',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'password',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::FIELD_TYPE_KEY => 'password',
                parent::EDIT_OPTIONS_KEY => [
                    'required' => false,
                ],
            ]
        ]);
    }

    public function prePersist($object)
    {
        $this->rehash($object);
    }

    public function preUpdate($object)
    {
        if (null != $object->getPassword()) {
            $this->rehash($object);
        } else {
            $manager = $this->getConfigurationPool()->getContainer()->get('Doctrine')->getManager();
            $uow = $manager->getUnitOfWork();
            $originalEntityData = $uow->getOriginalEntityData($object);
            $object->setPassword($originalEntityData['password']);
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
