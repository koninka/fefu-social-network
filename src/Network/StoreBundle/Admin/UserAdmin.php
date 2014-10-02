<?php

namespace Network\StoreBundle\Admin;

class UserAdmin extends VDolgahAdmin
{

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->configureFields([
            [
                'field' => 'login',
                'identifier' => true,
            ],
            [
                'field' => 'salt',
                'options' => [
                    'required' => false,
                    'read_only' => true
                ]
            ],
            [
                'field' => 'password',
            ]
        ]);
    }

} 
