<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('country', 'text', ['label' => 'form.contact.address.country']);
        $builder->add('city', 'text', ['label' => 'form.contact.address.city']);
        $builder->add('street', 'text', ['label' => 'form.contact.address.street']);
        $builder->add('house', 'text', ['label' => 'form.contact.address.house']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Network\StoreBundle\Entity\Address',
            'cascade_validation' => true,
            'translation_domain' => 'FOSUserBundle',
        ]);
    }

    public function getName()
    {
        return 'address';
    }
}
