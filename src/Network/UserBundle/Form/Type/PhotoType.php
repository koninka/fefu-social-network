<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Network\StoreBundle\Form\Type\BaseType;

class PhotoType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('media', 'sonata_media_type', ['label' => false, 'provider' => 'sonata.media.provider.image', 'context' => 'default'])
                ->add('description', 'textarea', ['label' => 'form.photo.description', 'required' => false]);

        $builder->get('media')->add('unlink', 'hidden', ['mapped' => false, 'data' => false]);
        $builder->get('media')->add('binaryContent', 'file', ['label' => false]);
    }

    public function getName()
    {
        return 'network_user_photo';
    }

}
