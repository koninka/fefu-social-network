<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Network\StoreBundle\Form\Type\BaseType;

class AlbumType extends BaseType
{

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'Application\Sonata\MediaBundle\Entity\Gallery', 'validation_groups' => false,]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['label' => 'Название'])
                ->add('context', 'textarea', ['label' => 'Описание', 'required' => false]);
    }

    public function getName()
    {
        return 'network_user_album';
    }

}
