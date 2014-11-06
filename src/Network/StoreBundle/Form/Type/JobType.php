<?php
namespace Network\StoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class JobType extends BaseType
{

    protected $entityClass = 'Network\StoreBundle\Entity\Job';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFieldsToBuilder($builder);
    }

    public function getName()
    {
        return 'job';
    }

}
