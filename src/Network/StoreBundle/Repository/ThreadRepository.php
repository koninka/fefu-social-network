<?php

namespace Network\StoreBundle\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Types\Type;

use Network\StoreBundle\Entity\Thread;
use Network\StoreBundle\Entity\User;

class ThreadRepository extends EntityRepository
{
    public function findByUsers($userId1, $userId2)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Network\StoreBundle\Entity\Thread', 't');
        $rsm->addFieldResult('t', 'id', 'id');
        $rsm->addFieldResult('t', 'topic', 'topic');

        $sql =
       "SELECT t6.* FROM thread as t6
        INNER JOIN
            (SELECT DISTINCT t2.thread_id FROM
                (SELECT t1.thread_id, count(t1.user_id) AS n FROM threads_users AS t1
                 GROUP BY t1.thread_id) as t2
        INNER JOIN threads_users AS t3
        ON (t3.thread_id = t2.thread_id AND n = 2 AND t3.user_id = ?)
        INNER JOIN threads_users AS t4
        ON (t4.thread_id = t2.thread_id AND n = 2 AND t4.user_id = ?)) as t7
        ON (t6.id = t7.thread_id)";

        $query = $em->createNativeQuery($sql, $rsm);

        $query->setParameter(1, $userId1);
        $query->setParameter(2, $userId2);

        $r = $query->getResult();

        return $r;
    }

    public function getThreadListForUser($userId)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Network\StoreBundle\Entity\Thread', 't');
        $rsm->addFieldResult('t', 'id', 'id');
        $rsm->addFieldResult('t', 'topic', 'topic');

        $sql =
       "SELECT t1.* FROM thread as t1
        INNER JOIN threads_users as t2
        ON (t1.id = t2.thread_id AND t2.user_id = ?)";

        $query = $em->createNativeQuery($sql, $rsm);

        $query->setParameter(1, $userId);

        $r = $query->getResult();

        return $r;
    }

    public function getOtherUserInThread($threadId, $userId)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Network\StoreBundle\Entity\User', 'u');
        $rsm->addFieldResult('u', 'id', 'id');
        $rsm->addFieldResult('u', 'firstname', 'firstName');
        $rsm->addFieldResult('u', 'lastname', 'lastName');

        $sql =
       "SELECT t1.* FROM user as t1
        INNER JOIN threads_users as t2
        ON (t1.id = t2.user_id AND t2.user_id != ? AND t2.thread_id = ?)";

        $query = $em->createNativeQuery($sql, $rsm);

        $query->setParameter(1, $userId);
        $query->setParameter(2, $threadId);

        $r = $query->getOneOrNullResult();

        return $r;
    }
}
