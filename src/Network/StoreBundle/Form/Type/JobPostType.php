<?php
namespace Network\StoreBundle\Form\Type;

use Network\StoreBundle\Form\Type\SearchableFieldType;

class JobPostType extends SearchableFieldType
{

    protected $entityClass = 'JobPost';

    public function getName()
    {
        return 'network_storebundle_job_post';
    }

} 
