<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Network\StoreBundle\Form\Type\UserType;

class ChangeEmailType extends UserType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', ['label' => 'form.new_email'])
                ->add('plainPassword', 'password', ['label' => 'form.password']);
    }

    public function getName()
    {
        return 'network_user_change_email';
    }

}
