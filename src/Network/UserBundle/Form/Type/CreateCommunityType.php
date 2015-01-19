<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Network\StoreBundle\DBAL\TypeCommunityEnumType;

class CreateCommunityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', []);
        $builder->add('view', 'choice', [
            'choices' => Type::getType('viewCommunityEnumType')->getChoices()
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Network\StoreBundle\Entity\Community',
            'cascade_validation'  => true,
        ]);
    }

    public function getName()
    {
        return 'community';
    }
}

