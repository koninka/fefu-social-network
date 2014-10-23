<?php

namespace Network\StoreBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Doctrine\DBAL\Types\Type;

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
                parent::TYPE_KEY => 'password',
                parent::EDIT_OPTIONS_KEY => [
                    'required' => false,
                ],
            ],
            [
                parent::FIELD_KEY => 'gender',
                parent::TYPE_KEY => 'sonata_type_choice_field_mask',
                parent::OPTIONS_KEY => [
                    'choices' => Type::getType('genderEnumType')->getChoices(),
                ],
            ],
        ]);
    }

    public function prePersist($object)
    {
        $encoder = $this->getConfigurationPool()
                        ->getContainer()
                        ->get('security.encoder_factory')
                        ->getEncoder($object);
        $object->rehash($encoder);
    }

    public function preUpdate($object)
    {
        if (null != $object->getPassword()) {
            $encoder = $this->getConfigurationPool()
                ->getContainer()
                ->get('security.encoder_factory')
                ->getEncoder($object);
            $object->rehash($encoder);
        } else {
            $manager = $this->getConfigurationPool()->getContainer()->get('Doctrine')->getManager();
            $uow = $manager->getUnitOfWork();
            $originalEntityData = $uow->getOriginalEntityData($object);
            $object->setPassword($originalEntityData['password']);
        }
    }

} 
