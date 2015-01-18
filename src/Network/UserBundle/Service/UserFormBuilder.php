<?php

namespace Network\UserBundle\Service;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\Form\FormBuilderInterface;

class UserFormBuilder
{

    static function baseBuildForm(FormBuilderInterface $builder)
    {
        $builder->remove('username')
            ->add('firstName', 'text', ['attr' => ['placeholder' => 'Имя',], 'label' => 'Имя'])
            ->add('lastName', 'text', ['attr' => ['placeholder' => 'Фамилия',], 'label' => 'Фамилия'])
            ->add('gender', 'choice', ['label' => 'Пол', 'choices' => Type::getType('genderEnumType')->getChoices()]);
    }

}
