<?php

namespace Network\UserBundle\Service;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\Form\FormBuilderInterface;

class UserFormBuilder
{

    static function baseBuildForm(FormBuilderInterface $builder)
    {
        $builder->remove('username')
            ->add('firstName', null, ['label' => 'Имя'])
            ->add('lastName', null, ['label' => 'Фамилия'])
            ->add('gender', 'choice', ['label' => 'Пол', 'choices' => Type::getType('genderEnumType')->getChoices()]);
    }

}
