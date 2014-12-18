<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('country', 'text', [
            'cascade_validation'  => true,
        ]);
        $builder->add('city', 'text', [
            'cascade_validation'  => true,
        ]);
        $builder->add('street', 'text', [
            'cascade_validation'  => true,
        ]);
        $builder->add('house', 'text', [
            'cascade_validation'  => true,
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Network\StoreBundle\Entity\Address',
        ]);
    }

    public function getName()
    {
        return 'address';
    }
}
