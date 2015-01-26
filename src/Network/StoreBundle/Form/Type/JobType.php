<?php
namespace Network\StoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class JobType extends BaseType
{

    protected $entityClass = 'Network\StoreBundle\Entity\Job';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFieldsToBuilder($builder);
        $builder->add('post', 'network_storebundle_job_post', [
            'attr' => [
                'class' => 'vdolgah_searchable_field_by_name',
            ],
            'label' => 'form.post',
            'translation_domain' => 'FOSUserBundle',
        ]);
    }

    public function getName()
    {
        return 'job';
    }

}
