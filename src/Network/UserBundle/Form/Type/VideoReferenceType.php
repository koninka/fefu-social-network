<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Network\StoreBundle\Form\Type\BaseType;

class VideoReferenceType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'media',
            'sonata_media_type',
            [
                'label'    => false,
                'provider' => 'sonata.media.provider.youtube',
                'context'  => 'video'
            ]
        )
                ->add(
                'name',
                'text',
                [
                    'label'    => 'form.video.name',
                    'required' => false
                ]
            )
                ->add(
                'description',
                'textarea',
                [
                    'label'    => 'form.video.description',
                    'required' => false
                ]
            );

        $builder->get('media')
            ->add(
                'unlink',
                'hidden',
                [
                    'mapped' => false,
                    'data'   => false
                ]
            );

        $builder->get('media')
            ->add(
                'binaryContent',
                'text',
                [
                    'label'    => 'form.video.link'
                ]
            );
    }

    public function getName()
    {
        return 'network_user_video_reference';
    }
}
