<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\DBAL\Types\Type;
use FOS\UserBundle\Form\Type\ProfileFormType as UserType;

class ProfileFormType extends UserType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('username')
                ->add('email', 'email', ['label' => 'E-mail', 'translation_domain' => 'FOSUserBundle'])
                ->add('firstName', null, ['label' => 'Имя'])
                ->add('lastName', null, ['label' => 'Фамилия'])
                ->add('gender', 'choice', ['label' => 'Пол', 'choices' => Type::getType('genderEnumType')->getChoices()])
                ->add('birthday', 'date', ['widget' => 'single_text', 'input' => 'datetime', 'attr' => ['class' => 'datepicker']]);
    }

    public function getName()
    {
        return 'network_user_profile';
    }

}
