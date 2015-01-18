<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('country', 'text', []);
        $builder->add('city', 'text', []);
        $builder->add('street', 'text', []);
        $builder->add('house', 'text', []);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Network\StoreBundle\Entity\Address',
            'cascade_validation'  => true,
        ]);
    }

    public function getName()
    {
        return 'address';
    }
}
