<?php

namespace Network\StoreBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Validator\Constraints as Assert;
use \Network\StoreBundel\Entity\Phonenumber;
use Network\StoreBundle\Admin\VDolgahAdmin as Admin;
/**
 * Admin class for country, city, university, school, faculty and chair
 */
class SchoolAdmin extends Admin
{
    const ENTITY_BASE_DIR = 'Network\\StoreBundle\\Entity\\';

    /**
     * {@inheritdoc}
     */
    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $options = [
            [
                parent::FIELD_KEY => 'country',
                parent::NOT_SHOW_IN_CHILD_KEY => true,
                parent::OPTIONS_KEY => [
                    'required' => true
                ]
            ]
        ];
        foreach (['city', 'university', 'faculty'] as $f) {
            $options[] = [
                parent::FIELD_KEY => $f,
                parent::TYPE_KEY => 'parent_category',
                parent::NOT_SHOW_IN_CHILD_KEY => true,
            ];
        }
        $fields = [
            'faculties' => 'Faculty',
            'chairs' => 'Chair',
        ];
        foreach ($fields as $field => $class) {
            $options[] = [
                parent::FIELD_KEY => $field,
                parent::TYPE_KEY => 'sonata_type_model',
                parent::OPTIONS_KEY => [
                    'class' => 'NetworkStoreBundle:' . $class,
                    'multiple' => true,
                    'required' => false,
                    'by_reference' => false,
                ],
                parent::QUERY => $this->genDQL($class)
            ];
        }
        foreach (['cities', 'universities', 'schools'] as $field) {
            $options[] = [
                parent::FIELD_KEY => $field,
                parent::NOT_SHOW_IN_LIST_KEY => true,
                parent::NOT_SHOW_IN_FORM_KEY => true,
            ];
        }
        $this->configureFields($options);
    }

    /**
     * @param string $class
     *
     * @return string DLQ
     */
    protected function genDQL($class)
    {
        $c = self::ENTITY_BASE_DIR . $class;
        $parent = strtolower($c::getParent());

        return "SELECT e FROM NetworkStoreBundle:$class e WHERE e.$parent IS NULL OR e.$parent = :id";
    }

    /**
     * @param string $dql
     *
     * @return Query
     */
    protected function addQuery($dql)
    {
        $obj = $this->getSubject();
        $query = null;
        if ($obj) {
            $query = $this->getModelManager()
                ->getEntityManager($this->getClass())
                ->createQuery($dql)
                ->setParameter('id', $obj->getId());
        }

        return $query;
    }

    /**
     * @param array $field
     *
     * @return string
     */
    protected function getFieldType($field)
    {
        $type = null;
        if (array_key_exists(self::TYPE_KEY, $field)) {
            $type = $field[self::TYPE_KEY];
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    protected function addFieldToMapper($mapper, $field)
    {
        $type = $this->getFieldType($field);
        if ($type == 'parent_category') {
            $class = $this->getClass();
            $data = $this->getSubject();
            if (method_exists($class, 'getParent')) {
                $parent = $class::getParent();
                $this->addParent($mapper, self::ENTITY_BASE_DIR . $parent, true, $data);
            }

            return;
        }
        parent::addFieldToMapper($mapper, $field);
    }

    /**
     * @param Mapper $mapper
     * @param string $class
     * @param bool   $mapped
     * @param Entity $data
     */
    public function addParent($mapper, $class, $mapped, $data)
    {
        $parent = false;
        $r = new \ReflectionClass($class);

        $baseNameClass = $r->getShortName();
        $getter = 'get' . $baseNameClass;
        if (method_exists($class, 'getParent')) {
            $parent = $class::getParent();
            $e = $data ? $data->$getter() : null;
            $this->addParent($mapper, self::ENTITY_BASE_DIR . $parent, false, $e);
        }
        $options = [
            'class' => 'NetworkStoreBundle:' . $baseNameClass,
            'multiple' => false,
            'model_manager' => $this->getModelManager(),
            'attr' => [
                'category' => $baseNameClass
            ],
            'mapped' => $mapped,
        ];
        $type = 'sonata_type_model';
        if ($parent) {
            $options['attr']['parent_category'] = $parent;
            $type = 'category';
        }
        if ($data && !$mapped) {
            $options['data'] = $data->$getter();
        }
        $mapper->add($baseNameClass, $type, $options);
    }

    protected $formOptions = array(
        'cascade_validation' => true
    );
}
