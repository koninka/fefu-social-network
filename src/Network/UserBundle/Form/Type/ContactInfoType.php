<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Network\UserBundle\Form\Type\PhoneType;
use Network\UserBundle\Form\Type\AddressType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class ContactInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('skype', 'text', [
            'cascade_validation'  => true,
            'error_bubbling' => true, 
            'required' => false
        ]);
        $builder->add('additionalEmail', 'email', [
            'cascade_validation'  => true,
            'error_bubbling' => true, 
            'required' => false
        ]);
        $builder->add('address', 'collection', [
            'type' => new AddressType(),
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => true,
            'cascade_validation'  => true,
        ]);
        $builder->add('phone', 'collection', [
            'type' => new PhoneType(),
            'allow_add' => true,
            'allow_delete' => true,
            'cascade_validation'  => true,
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Network\StoreBundle\Entity\ContactInfo',
            'cascade_validation'  => true,
        ]);
    }

    public function getName()
    {
        return 'ContactInfo';
    }
}
