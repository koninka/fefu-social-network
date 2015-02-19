<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.01.2015
 * Time: 19:51
 */

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Form\AbstractType as BaseType;
use Network\UserBundle\Service\UserFormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ResumeRegistrationType extends BaseType
{
    private $class;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('username')
            ->add('firstName', 'text', ['attr' => ['placeholder' => 'Имя',],'label' => 'Имя','required' => false])
            ->add('lastName', 'text', ['attr' => ['placeholder' => 'Фамилия',],'label' => 'Фамилия','required' => false])
            ->add('gender', 'choice', ['label' => 'Пол', 'choices' => Type::getType('genderEnumType')->getChoices()]);
        $builder->add('email', 'email', ['label' => false, 'attr' => ['placeholder' => 'E-mail',], 'translation_domain' => 'FOSUserBundle'])
            ->add('plainPassword', 'repeated', [
                'type'            => 'password',
                'options'         => ['translation_domain' => 'FOSUserBundle'],
                'first_options'   => ['attr' => ['placeholder' => 'Пароль',],'label' => false],
                'second_options'  => ['attr' => ['placeholder' => 'Подтвердите пароль',],'label' => false],
                'invalid_message' => 'Введенные пароли не совпадают!',
            ]);
        $builder->remove('plain_Password');
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
        });
    }

    public function getName()
    {
        return 'network_user_resume_registration';
    }

}