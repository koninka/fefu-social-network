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
                'label' => 'form.job.name',
                'translation_domain' => 'FOSUserBundle',
            ]
        );
        $builder->add('save', 'submit', ['label' => 'form.job.save.submit', 'translation_domain' => 'FOSUserBundle',]);
    }

    public function getName()
    {
        return 'network_storebundle_base_collection';
    }

}
