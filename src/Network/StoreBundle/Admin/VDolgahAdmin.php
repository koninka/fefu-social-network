<?php

namespace Network\StoreBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class VDolgahAdmin extends Admin
{

    const FIELD_KEY = 'field';
    const IDENTIFIER_KEY = 'identifier';
    const OPTIONS_KEY = 'options';
    const NOT_SHOW_IN_LIST_KEY = 'not_show_in_list';
    const NOT_SHOW_IN_FORM_KEY = 'not_show_in_form';

    protected $fields = [];

    protected function configureFields($options)
    {
        foreach ($options as $option) {
            foreach ($this->fields as $idx => $field) {
                if ($field['name'] === $option[VDolgahAdmin::FIELD_KEY]) {
                    $this->fields[$idx] = array_merge($this->fields[$idx], $option);
                    break;
                }
            }
        }
    }

    protected function addFieldToMapper($mapper, $field)
    {
        $options = [];
        if (array_key_exists(VDolgahAdmin::OPTIONS_KEY, $field)) {
            $options = $field[VDolgahAdmin::OPTIONS_KEY];
        }
        $mapper->add($field['name'], null, $options);
    }

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $entityReflection = new \ReflectionClass($class);
        foreach ($entityReflection->getProperties() as $property) {
            $name = ucfirst($property->getName());
            if ($entityReflection->hasMethod("set" . $name) && $entityReflection->hasMethod("get" . $name)) {
                $this->fields[] = [ 'name' => $property->getName() ];
            }
        }
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        foreach ($this->fields as $field){
            if (
                array_key_exists(VDolgahAdmin::IDENTIFIER_KEY, $field)
                && $field[VDolgahAdmin::IDENTIFIER_KEY]
            ) {
                $listMapper->addIdentifier($field['name']);
            } elseif (
                !array_key_exists(VDolgahAdmin::NOT_SHOW_IN_LIST_KEY, $field)
                || false == $field[VDolgahAdmin::NOT_SHOW_IN_LIST_KEY]
            ) {
                $this->addFieldToMapper($listMapper, $field);
            }
        }
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
        foreach ($this->fields as $field) {
            if (
                !array_key_exists(VDolgahAdmin::NOT_SHOW_IN_FORM_KEY, $field)
                || false == $field[VDolgahAdmin::NOT_SHOW_IN_FORM_KEY]
            ) {
                $this->addFieldToMapper($formMapper, $field);
            }
        }
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        foreach ($this->fields as $field) {
            $this->addFieldToMapper($showMapper, $field);
        }
    }

} 
