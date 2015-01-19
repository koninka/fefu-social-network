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
        $dql = "
            SELECT
                p.id as id,
                p.ts as ts,
                p.text as text,
                CONCAT(u.firstName, CONCAT(' ', u.lastName)) as author,
                u.id as userId
            FROM NetworkStoreBundle:Post p
            JOIN p.user u
            JOIN p.thread t WHERE t.id=:id";
        $query = $em->CreateQuery($dql)->setParameter('id', $threadId);
        $r = $query->getResult();

        return $r;
    }
}