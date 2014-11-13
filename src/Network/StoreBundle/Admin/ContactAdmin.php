<?php

namespace Network\StoreBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Validator\Constraints as Assert;
use \Network\StoreBundel\Entity\Phonenumber;

class ContactAdmin extends VDolgahAdmin
{

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->configureFields([
            [
                parent::FIELD_KEY => 'additionalEmail',
                parent::TYPE_KEY => 'email',
                parent::OPTIONS_KEY => [
                    'required' => false,
                ],
            ],
            [
                parent::FIELD_KEY => 'address',
                parent::TYPE_KEY => 'sonata_type_model',
                parent::OPTIONS_KEY => [
                    'label' => 'Address',
                    'class'=>'NetworkStoreBundle:Address',
                    'required' => false,
                    'multiple' => true,
                ],
            ],
            [
                parent::FIELD_KEY => 'contactInfo',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
            [
                parent::FIELD_KEY => 'phone',
                parent::TYPE_KEY => 'sonata_type_model',
                parent::OPTIONS_KEY => [
                    'label' => 'Phonenumber',
                    'by_reference' => false,
                    'class'=>'NetworkStoreBundle:Phonenumber',
                    'multiple' => true,
                ],
                parent::QUERY => 'SELECT p FROM NetworkStoreBundle:Phonenumber p WHERE p.contactInfo IS NULL
                    OR p.contactInfo = :id',
            ],
            [
                parent::FIELD_KEY => 'user',
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ],
        ]);
    }

    protected $formOptions = array(
        'cascade_validation' => true
     );

}