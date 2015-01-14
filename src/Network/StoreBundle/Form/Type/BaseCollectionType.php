<?php
namespace Network\StoreBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class BaseCollectionType extends CollectionType
{

    protected $baseType = null;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $this->baseType . 's',
            'collection',
            [
                'type' => $this->baseType,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'options' => [
                    'label' => false,
                ],
            ]
        );
        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'network_storebundle_base_collection';
    }

}
