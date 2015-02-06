<?php

namespace Network\UserBundle\Service;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\Form\FormBuilderInterface;

class UserFormBuilder
{

    static function baseBuildForm(FormBuilderInterface $builder)
    {
        $builder->remove('username')
            ->add('firstName', 'text', [
                'attr' => ['placeholder' => 'form.profile.firstName'],
                'label' => 'form.profile.firstName',
                'translation_domain' => 'FOSUserBundle'])
            ->add('lastName', 'text', [
                'attr' => ['placeholder' => 'form.profile.lastName'],
                'label' => 'form.profile.lastName',
                'translation_domain' => 'FOSUserBundle']);
        $genderArr = [];
        $arr = Type::getType('genderEnumType')->getChoices();
        foreach($arr as $key => $value) {
            $genderArr[$key] = 'form.gender.'.$key;
        }
        $builder->add('gender', 'choice', [
            'label' => 'form.profile.gender',
            'choices' => $genderArr,
            'translation_domain' => 'FOSUserBundle']);
    }

}
