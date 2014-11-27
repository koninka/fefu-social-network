<?php

namespace Network\UserBundle\Service;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\Form\FormBuilderInterface;

class UserFormBuilder
{

    static function baseBuildForm(FormBuilderInterface $builder)
    {
        $builder->remove('username')
            ->add('firstName', 'text', ['attr' => ['placeholder' => 'Имя',],'label' => false])
            ->add('lastName', 'text', ['attr' => ['placeholder' => 'Фамилия',],'label' => false])
            ->add('gender', 'choice', ['label' => false, 'choices' => Type::getType('genderEnumType')->getChoices()]);
    }

}
