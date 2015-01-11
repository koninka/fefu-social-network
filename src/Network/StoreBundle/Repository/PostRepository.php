<?php

namespace Network\StoreBundle\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Types\Type;

use Network\StoreBundle\Entity\Post;

class PostRepository extends EntityRepository
{
    public function getThreadPosts($threadId)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Network\StoreBundle\Entity\Post', 'p');
        $rsm->addFieldResult('p', 'id', 'id');
        $rsm->addFieldResult('p', 'ts', 'ts');
        $rsm->addFieldResult('p', 'text', 'text');
        $rsm->addMetaResult('p', 'user_id', 'user_id');

        $sql = "SELECT t1.* FROM post as t1 WHERE t1.thread_id=?";

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter(1, $threadId);
        $r = $query->getResult();

        return $r;
    }
}