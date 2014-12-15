<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ChangePasswordFormType as BaseType;

class ChangePasswordType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', 'repeated', array(
            'type'            => 'password',
            'options'         => ['translation_domain' => 'FOSUserBundle'],
            'first_options'   => ['label' => 'Пароль'],
            'second_options'  => ['label' => 'Подтвердите пароль'],
            'invalid_message' => 'Введенные пароли не совпадают!',
        ));
    }

    public function getName()
    {
        return 'network_user_change_password';
    }

}

