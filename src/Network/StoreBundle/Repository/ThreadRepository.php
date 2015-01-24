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
        $dql = "
            SELECT thread from NetworkStoreBundle:Thread thread
            JOIN thread.userThreads ut
            JOIN thread.userThreads ut2
            WHERE ut.user = :u1_id AND ut2.user = :u2_id
            and thread.id in (select t.id from NetworkStoreBundle:Thread t
                join t.userThreads ut3
                GROUP BY t.id
                having count(ut3) = 2
            )
        ";

        $query = $em->createQuery($dql)
                    ->setParameter('u1_id', $userId1)
                    ->setParameter('u2_id', $userId2);

        $r = $query->getResult();

        return $r;
    }

    public function getThreadListForUser($userId)
    {
        //sorted by last post date
        $em = $this->getEntityManager();
        $dql =
       "SELECT
          t1.id as id,
          t1.topic as topic,
          ut1.unreadPosts as unreadPosts,
          (
            SELECT max(p2.ts) FROM NetworkStoreBundle:Post p2
	        WHERE p2.thread = ut1.thread
	        GROUP BY p2.thread
          ) as lastDate
        FROM NetworkStoreBundle:Thread t1
        JOIN t1.userThreads ut1
        WHERE ut1.user = :id
        ORDER BY lastDate DESC";
        $query = $em->createQuery($dql)->setParameter('id', $userId);
        $r = $query->getResult();

        return $r;
    }

    public function getUnreadPostsByUser($threadId, $userId)
    {
        $em = $this->getEntityManager();
        $dql =
            "SELECT
          ut.unreadPosts as unreadPosts
          FROM NetworkStoreBundle:UserThread ut
        WHERE ut.user = :user_id and ut.thread = :thread_id";
        $query = $em->createQuery($dql)
                    ->setParameter('thread_id', $threadId)
                    ->setParameter('user_id', $userId);

        return $query->getSingleResult()['unreadPosts'];
    }

    public function getThreadByIdAndUser($threadId, $userId)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT t FROM NetworkStoreBundle:Thread t
                JOIN t.userThreads t2
                WHERE t2.thread = :thread_id AND t2.user = :user_id";
        $query = $em->createQuery($dql)
            ->setParameter('user_id', $userId)
            ->setParameter('thread_id', $threadId);

        return $query->getOneOrNullResult();
    }

    public function checkPermission($threadId, $userId)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT t FROM NetworkStoreBundle:UserThread t
                WHERE t.thread = :thread_id AND t.user = :user_id";
        $query = $em->createQuery($dql)
                    ->setParameter('user_id', $userId)
                    ->setParameter('thread_id', $threadId);
        $r = $query->getOneOrNullResult();

        return $r != null;
    }

    public function getOtherUserInThread($threadId, $userId)
    {
        $em = $this->getEntityManager();
        $dql = "
            SELECT u FROM NetworkStoreBundle:User u
            JOIN u.userThreads ut
            WHERE ut.thread = :thread_id AND ut.user != :user_id";

        $query = $em->createQuery($dql)
                    ->setParameter('user_id', $userId)
                    ->setParameter('thread_id', $threadId);

        $r = $query->getOneOrNullResult();

        return $r;
    }

    public function getUserByWallThreadId($threadId)
    {
        $em = $this->getEntityManager();
        $dql = "
            SELECT u FROM NetworkStoreBundle:User u
            JOIN u.wallThreads w
            WHERE w.id = :thread_id";

        $query = $em->createQuery($dql)
                    ->setParameter('thread_id', $threadId);

        $r = $query->getOneOrNullResult();

        return $r;
    }
}
