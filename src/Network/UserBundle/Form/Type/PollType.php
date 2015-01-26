<?php

namespace Network\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Network\UserBundle\Form\Type\AnswerPollType;

class PollType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('question', 'textarea', []);
        $builder->add('isAnonymously', 'checkbox', [
            'required'  => false,
            'label' => 'Anonymously',
        ]);
        $builder->add('answers', 'collection', [
            'type' => new AnswerPollType(),
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Network\StoreBundle\Entity\Poll',
            'cascade_validation'  => true,
        ]);
    }

    public function getName()
    {
        return 'poll';
    }
}
