<?php

namespace Network\StoreBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Doctrine\DBAL\Types\Type;
use Sonata\AdminBundle\Show\ShowMapper;

class GroupAdmin extends VDolgahAdmin
{

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->configureFields([
            [
                parent::FIELD_KEY => 'name',
                parent::IDENTIFIER_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'roles',
                parent::TYPE_KEY => 'collection',
                parent::OPTIONS_KEY => [
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'type'   => 'choice',
                    'options' => ['choices' => Type::getType('roleEnumType')->getChoices()]
                ],
                static::NOT_SHOW_IN_LIST_KEY => true,
            ],
        ]);
    }
}
