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

        $builder->add('avatar', 'sonata_media_type', [
            'label' => false,
            'provider' => 'sonata.media.provider.image',
            'context' => 'avatar',
            'required' => false
        ]);
        $builder->get('avatar')->add('unlink', 'hidden', ['mapped' => false, 'data' => false]);
        $builder->get('avatar')->add('binaryContent', 'file', ['label' => false]);

        UserFormBuilder::baseBuildForm($builder);
        $builder->add('birthday', 'date', [
            'label' => 'form.profile.date',
            'translation_domain' => 'FOSUserBundle',
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
