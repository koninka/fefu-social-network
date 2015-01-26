<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Network\StoreBundle\DBAL\TypeCommunityEnumType;

class CreateCommunityType extends AbstractType
{

    public function prepareChoice()
    {
        $res = [];
        $arr = Type::getType('viewCommunityEnumType')->getChoices();
        foreach($arr as $key => $value) {
            $res[$key] = 'form.community.view_type.'.$key;
        }

        return $res;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['label' => 'community.create.name']);
        $builder->add('view', 'choice', [
            'choices' => $this->prepareChoice(),
            'label' => 'community.create.view',
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Network\StoreBundle\Entity\Community',
            'cascade_validation' => true,
            'translation_domain' => 'FOSUserBundle',
        ]);
    }

    public function getName()
    {
        return 'community';
    }
}

