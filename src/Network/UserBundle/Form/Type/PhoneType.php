<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PhoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('phonenumber', 'text', [
            'error_bubbling' => true,
            'label' => 'form.contact.phone.name'
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Network\StoreBundle\Entity\Phonenumber',
            'cascade_validation' => true,
            'translation_domain' => 'FOSUserBundle',
        ]);
    }

    public function getName()
    {
        return 'phonenumber';
    }
}
