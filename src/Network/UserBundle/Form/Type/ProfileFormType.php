<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\DBAL\Types\Type;
use FOS\UserBundle\Form\Type\ProfileFormType as UserType;
use Network\UserBundle\Service\UserFormBuilder;

class ProfileFormType extends UserType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        UserFormBuilder::baseBuildForm($builder);
        $builder->add('birthday', 'date', [
            'label' => 'Дата рождения',
            'widget' => 'single_text',
            'input' => 'datetime',
            'attr' => ['class' => 'datepicker']
        ]);
    }

    public function getName()
    {
        return 'network_user_profile';
    }

}
