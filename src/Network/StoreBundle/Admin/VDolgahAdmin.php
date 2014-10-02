<?php

namespace Network\StoreBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class VDolgahAdmin extends Admin
{

    protected $fields = [];

    protected function configureFields($options)
    {
        foreach ($options as $option)
        {
            foreach ($this->fields as $idx => $field)
                if ($field['name'] === $option['field'])
                {
                    $this->fields[$idx] = array_merge($this->fields[$idx], $option);
                    break;
                }
        }
    }

    protected function addFieldsToMapper($mapper)
    {
        foreach ($this->fields as $field)
        {
            if (
                array_key_exists('identifier', $field)
                && $field['identifier']
                && method_exists($mapper, 'addIdentifier')
            )
                $mapper->addIdentifier($field['name']);
            else {
                $options = [];
                if (array_key_exists('options', $field))
                    $options = $field['options'];
                $mapper->add($field['name'], null, $options);

            }
        }
    }

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $entityReflection = new \ReflectionClass($class);
        foreach ($entityReflection->getProperties() as $property)
        {
            $name = ucfirst($property->getName());
            if ($entityReflection->hasMethod("set" . $name) && $entityReflection->hasMethod("get" . $name))
                $this->fields[] = [ 'name' => $property->getName() ];
        }
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $this->addFieldsToMapper($listMapper);
        $listMapper->add('_action', 'actions', [
            'actions' => [
                'show' => [],
                'edit' => [],
                'delete' => []
            ]
        ]);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->addFieldsToMapper($formMapper);
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $this->addFieldsToMapper($showMapper);
    }

} 
