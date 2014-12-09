<?php

namespace Network\StoreBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\PropertyAccess\PropertyAccess;

class VDolgahAdmin extends Admin
{

    const FIELD_KEY = 'field';
    const IDENTIFIER_KEY = 'identifier';
    const OPTIONS_KEY = 'options';
    const TYPE_KEY = 'type';
    const EDIT_OPTIONS_KEY = 'edit_options';
    const OPTIONS_KEY_DESCRIPTION = 'edit_description';
    const NOT_SHOW_IN_LIST_KEY = 'not_show_in_list';
    const NOT_SHOW_IN_FORM_KEY = 'not_show_in_form';
    const QUERY = 'query';

    protected $fields = [];

    protected function editing()
    {
        return $this->id($this->getSubject());
    }

    protected function configureFields($options)
    {
        foreach ($options as $option) {
            foreach ($this->fields as $idx => $field) {
                if ($field['name'] === $option[self::FIELD_KEY]) {
                    $this->fields[$idx] = array_merge($this->fields[$idx], $option);
                    break;
                }
            }
        }
    }

    protected function addFieldToMapper($mapper, $field)
    {
        $options = [];
        if (
            array_key_exists(self::EDIT_OPTIONS_KEY, $field)
            && $this->editing()
        ) {
            $options = $field[self::EDIT_OPTIONS_KEY];
        } elseif (array_key_exists(self::OPTIONS_KEY, $field)) {
            $options = $field[self::OPTIONS_KEY];
        }
        $type = null;
        if (array_key_exists(self::TYPE_KEY, $field)) {
            $type = $field[self::TYPE_KEY];
        }
        if ($type == 'date') {
            $options['widget'] = 'single_text';
            $options['input'] = 'datetime';
            $options['attr'] = ['class' => 'datepicker'];
        }
        if (array_key_exists(self::QUERY, $field)) {
            $query = $this->addQuery($field[self::QUERY]);
            if ($query != null) {
                $options['query'] = $query;
            }
        }
        if (array_key_exists(self::OPTIONS_KEY_DESCRIPTION, $field)) {
            $mapper->add($field['name'], $type, $options, $field[self::OPTIONS_KEY_DESCRIPTION]);
        } else {
            $mapper->add($field['name'], $type, $options);
        }
    }

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $entity = new $class;
        $accessor = PropertyAccess::createPropertyAccessor();
        $entityReflection = new \ReflectionClass($class);
        foreach ($entityReflection->getProperties() as $property) {
            $propertyName = $property->getName();
            if (
                $accessor->isWritable($entity, $propertyName) &&
                $accessor->isReadable($entity, $propertyName)
            ) {
                $this->fields[] = [ 'name' => $propertyName ];
            }
        }
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        foreach ($this->fields as $field) {
            if (
                array_key_exists(self::IDENTIFIER_KEY, $field)
                && $field[self::IDENTIFIER_KEY]
            ) {
                $listMapper->addIdentifier($field['name']);
            } elseif (
                !array_key_exists(self::NOT_SHOW_IN_LIST_KEY, $field)
                || false == $field[self::NOT_SHOW_IN_LIST_KEY]
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
                !array_key_exists(self::NOT_SHOW_IN_FORM_KEY, $field)
                || false == $field[self::NOT_SHOW_IN_FORM_KEY]
            ) {
                $this->addFieldToMapper($formMapper, $field);
            }
        }
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        foreach ($this->fields as $field) {
            $type = array_key_exists(self::TYPE_KEY, $field) ? $field[self::TYPE_KEY] : null;
            if ($type === 'collection') {
                $field[self::TYPE_KEY] = 'array';
            }
            $this->addFieldToMapper($showMapper, $field);
        }
    }

}
