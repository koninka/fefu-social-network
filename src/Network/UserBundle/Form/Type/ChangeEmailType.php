<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Network\StoreBundle\Form\Type\BaseType;

class ChangeEmailType extends BaseType
{

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'Network\StoreBundle\Entity\User']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', ['label' => 'E-mail', 'translation_domain' => 'FOSUserBundle'])
                ->add('plainPassword', 'password', ['translation_domain' => 'FOSUserBundle', 'label' => 'Пароль']);
    }

    public function getName()
    {
        return 'network_user_change_email';
    }

}
