<?php

namespace Network\StoreBundle\Form\Type;

use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;

/**
 * Class CategoryType
 * type provides ajax loading of data dependent on parent data
 */
class CategoryType extends ModelType
{

    /**
     * @var ModelManager
     */
    private $mm;

    /**
     * @param ModelManager $mm
     */
    public function __construct(ModelManager $mm)
    {
        $this->mm = $mm;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['className'] = $options['class'];
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults([
            'model_manager' => $this->mm,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'category';
    }
}
