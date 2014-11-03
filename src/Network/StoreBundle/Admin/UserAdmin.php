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
                parent::FIELD_KEY => 'email',
                parent::IDENTIFIER_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'plainPassword',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'salt',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'emailCanonical',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'usernameCanonical',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'passwordRequestedAt',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'username',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'lastLogin',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'expiresAt',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'credentialsExpireAt',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'roles',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'confirmationToken',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'enabled',
                parent::OPTIONS_KEY => [
                    'required' => false,
                ],
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
            [
                parent::FIELD_KEY => 'birthday',
                parent::TYPE_KEY => 'birthday',
                parent::NOT_SHOW_IN_LIST_KEY => true, // TODO: extend \DateTime with own format of it
                parent::OPTIONS_KEY => [
                    'required' => false,
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
        $object->hash($encoder);
    }

    public function preUpdate($object)
    {
        if (null != $object->getPassword()) {
            $encoder = $this->getConfigurationPool()
                ->getContainer()
                ->get('security.encoder_factory')
                ->getEncoder($object);
            $object->hash($encoder);
        } else {
            $manager = $this->getConfigurationPool()->getContainer()->get('Doctrine')->getManager();
            $uow = $manager->getUnitOfWork();
            $originalEntityData = $uow->getOriginalEntityData($object);
            $object->setPassword($originalEntityData['password']);
        }
    }

}
