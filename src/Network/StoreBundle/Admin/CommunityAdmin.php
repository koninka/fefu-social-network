<?php
namespace Network\StoreBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Doctrine\DBAL\Types\Type;
use Network\StoreBundle\DBAL\TypeCommunityEnumType;


class CommunityAdmin extends VDolgahAdmin
{
    protected $formOptions = [
        'cascade_validation' => true
    ];

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->configureFields([
            [
                parent::FIELD_KEY => 'name',
                parent::IDENTIFIER_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'type',
                parent::TYPE_KEY => 'sonata_type_choice_field_mask',
                parent::OPTIONS_KEY => [
                    'choices' => Type::getType('typeCommunityEnumType')->getChoices()
                ],
                parent::NOT_SHOW_IN_LIST_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'view',
                parent::TYPE_KEY => 'sonata_type_choice_field_mask',
                parent::OPTIONS_KEY => [
                    'choices' => Type::getType('viewCommunityEnumType')->getChoices()
                ],
                parent::NOT_SHOW_IN_LIST_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'community',
                parent::NOT_SHOW_IN_FORM_KEY => true,
                parent::NOT_SHOW_IN_LIST_KEY => true,
            ],
        ]);
    }
}
