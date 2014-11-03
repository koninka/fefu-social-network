<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\DBAL\Types\Type;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('username')
                ->add('email', 'email', array('label' => 'E-mail', 'translation_domain' => 'FOSUserBundle'))
                ->add('plainPassword', 'repeated', array(
                    'type'            => 'password',
                    'options'         => array('translation_domain' => 'FOSUserBundle'),
                    'first_options'   => array('label' => 'Пароль'),
                    'second_options'  => array('label' => 'Подтвердите пароль'),
                    'invalid_message' => 'Введенные пароли не совпадают!',
                ))
                ->add('firstName', null, ['label' => 'Имя'])
                ->add('lastName', null, ['label' => 'Фамилия'])
                ->add('gender', 'choice', ['label' => 'Пол', 'choices' => Type::getType('genderEnumType')->getChoices()]);
    }

    public function getName()
    {
        return 'network_user_registration';
    }

}
