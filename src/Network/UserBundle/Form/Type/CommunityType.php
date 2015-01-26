<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Network\StoreBundle\DBAL\TypeCommunityEnumType;

class CommunityType extends AbstractType
{

    public function prepareChoice()
    {
        $res = [];
        $arr = Type::getType('typeCommunityEnumType')->getChoices();
        foreach($arr as $key => $value) {
            $res[$key] = 'form.community.type.'.$key;
        }

        return $res;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['label' => 'community.edit.name']);
        $builder->add('description', 'textarea', [
            'required' => false,
            'label' => 'community.edit.description',
        ]);
        $builder->add('subjects', 'entity', [
            'class' => 'NetworkStoreBundle:Subjects',
            'label' => 'community.edit.subjects',
        ]);
        $builder->add('type', 'choice', [
            'choices' => $this->prepareChoice(),
            'label' => 'community.edit.type',
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

