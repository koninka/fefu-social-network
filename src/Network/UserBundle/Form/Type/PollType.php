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
        $builder->add('question', 'textarea', [
            'label' => 'form.poll.question',
        ]);
        $builder->add('isAnonymously', 'checkbox', [
            'required' => false,
            'label' => 'form.poll.anonymously',
        ]);
        $builder->add('answers', 'collection', [
            'type' => new AnswerPollType(),
            'allow_add' => true,
            'allow_delete' => true,
            'label' => 'form.poll.answer',
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Network\StoreBundle\Entity\Poll',
            'cascade_validation' => true,
            'translation_domain' => 'FOSUserBundle',
        ]);
    }

    public function getName()
    {
        return 'poll';
    }
}
