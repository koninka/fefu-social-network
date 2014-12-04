<?php
namespace Network\StoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Network\StoreBundle\Form\DataTransformer;

class SearchableFieldType extends AbstractType
{

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var string
     */
    protected $entityClass = null;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformerClass = 'Network\StoreBundle\Form\DataTransformer\\' . $this->entityClass . 'ModelTransformer';
        $builder->addModelTransformer(new $transformerClass($this->om));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'network_storebundle_searchable_field';
    }

}
