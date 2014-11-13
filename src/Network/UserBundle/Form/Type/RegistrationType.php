<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\DBAL\Types\Type;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Network\UserBundle\Service\UserFormBuilder;

class RegistrationType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        UserFormBuilder::baseBuildForm($builder);
        $builder->add('email', 'email', ['label' => 'E-mail', 'translation_domain' => 'FOSUserBundle'])
                ->add('plainPassword', 'repeated', array(
                    'type'            => 'password',
                    'options'         => ['translation_domain' => 'FOSUserBundle'],
                    'first_options'   => ['label' => 'Пароль'],
                    'second_options'  => ['label' => 'Подтвердите пароль'],
                    'invalid_message' => 'Введенные пароли не совпадают!',
        ));
    }

    public function getName()
    {
        return 'network_user_registration';
    }

}
