<?php

namespace Network\StoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use \Doctrine\DBAL\Types\Type;

class BaseType extends AbstractType
{

    protected $entityClass = null;

    protected static $databaseType2FormType = [
        'string' => 'text',
        'text' => 'textarea',
        'date' => 'date',
        'integer' => 'integer',
    ];

    protected static $specificFormTypes = [
        'password',
        'birthday',
    ];

    protected function addFieldToBuilder(FormBuilderInterface $builder, array $params)
    {
        $name = $params['name'];
        $type = null;
        $options = [];
        if (array_key_exists($params['type'], self::$databaseType2FormType)) {
            $type = self::$databaseType2FormType[$params['type']];
            if (in_array($name, self::$specificFormTypes)) {
                $type = $name;
                if ($type == 'password') {
                    $options['required'] = false;
                }
            } else if ($type == 'date') {
                if (!array_key_exists('attr', $options)) {
                    $options['attr'] = [];
                }
                if (!array_key_exists('class', $options['attr'])) {
                    $options['attr']['class'] = 'datepicker';
                } else {
                    $options['attr']['class'] .= ' datepicker';
                }
                $options['format'] = 'yyyy-MM-dd';
                $options['widget'] = 'single_text';
            }
        } elseif (Type::hasType($params['type'])) {
            $dbType = Type::getType($params['type']);
            $type = $dbType->getFormType();
            $options = $dbType->getFormOptions();
        } else {
            throw \Exception(sprintf('No exist type for %s', $params['type']));
        }

        if (array_key_exists('nullable', $params) && $params['nullable'] === 'true') {
            $options['required'] = false;
        }
        if (array_key_exists('length', $params)) {
            $options['max_length'] = $params['length'];
        }

        $builder->add($name, $type, $options);
    }

    protected function addEntityFieldsToBuilder(FormBuilderInterface $builder)
    {
        $entityReflection = new \ReflectionClass($this->entityClass);
        foreach ($entityReflection->getProperties() as $property) {
            $argList = [];
            $doc = $property->getDocComment();
            $isColumn = preg_match_all('/\\\\Column\((.*)\)/x', $doc, $argList);
            $notShow = preg_match('/NotShowInForm!/', $doc); // TODO: think about more elegant way
            if (!$notShow && $isColumn) {
                $args = array_map('trim', preg_split('/,/', $argList[1][0]));
                $params = [];
                foreach ($args as $str) {
                    $parts = preg_split('/=/', $str);
                    $params[$parts[0]] = preg_replace('/"/', '', $parts[1]);
                }
                $this->addFieldToBuilder($builder, $params);
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFieldsToBuilder($builder);
        $builder->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->entityClass,
        ]);
    }

    public function getName()
    {
        return 'base';
    }

}
