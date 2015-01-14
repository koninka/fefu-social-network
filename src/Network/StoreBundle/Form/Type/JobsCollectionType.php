<?php
namespace Network\StoreBundle\Form\Type;


class JobsCollectionType extends BaseCollectionType
{

    protected $baseType = 'job';

    public function  getName()
    {
        return 'jobs';
    }

}
