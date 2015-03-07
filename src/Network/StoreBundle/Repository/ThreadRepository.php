<?php

namespace Network\StoreBundle\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Types\Type;

use Network\StoreBundle\DBAL\ThreadEnumType;
use Network\StoreBundle\DBAL\RoleCommunityEnumType;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
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
        $d =
        "SELECT t1.id as threadId, u.id as userId, CONCAT(u.firstName, CONCAT(' ', u.lastName)) as userName
        FROM NetworkStoreBundle:Thread t1
        JOIN t1.userThreads ut
        JOIN t1.userThreads ut2
        JOIN ut.user u
        WHERE t1.type = :dialogType AND ut.user != :userId AND ut2.user = :userId";
        $query = $em->createQuery($d)
            ->setParameter('dialogType', ThreadEnumType::T_DIALOG)
            ->setParameter('userId', $userId);
        $rr = $query->getResult();
        $w = [];
        foreach ($rr as $v) {
            $w[$v['threadId']] = ['userId' => $v['userId'], 'userName' => $v['userName']];
        }

        return ['items' => $r, 'helpMap' => $w];
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

    public function getUsersInThread($threadId)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT u.id, u.firstName, u.lastName FROM NetworkStoreBundle:User u INDEX BY u.id
                JOIN u.userThreads ut
                WHERE ut.thread = :thread_id";
        $query = $em->createQuery($dql)
            ->setParameter('thread_id', $threadId);

        return $query->getArrayResult();
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

    /**
     * @param $userId
     * @return array
     */
    public function getFeedForUser($userId)
    {
        $em = $this->getEntityManager();

        $dql1 = "
            SELECT wt.id as threadId, p.text, p.ts, u.firstName, u.lastName, u.id as user FROM NetworkStoreBundle:User u
            JOIN u.wallThreads wt
            JOIN wt.posts p
            WHERE u.id in(
             SELECT rp.id FROM NetworkStoreBundle:Relationship r
              JOIN r.partner rp
              WHERE r.user = :user_id and (r.status = :status1 or r.status = :status2)
            )";

        $dql2 = "
            SELECT wt.id as threadId, p.text, p.ts, c.id as comm_id, c.name as comm_name FROM NetworkStoreBundle:Community c
            JOIN c.wallThreads wt
            JOIN wt.posts p
            WHERE c.id in(
              SELECT com.id FROM NetworkStoreBundle:UserCommunity us
              JOIN us.community com
              JOIN us.user u
              WHERE u.id = :user_id and (us.role = :role1 or us.role = :role2)
            )
        ";

        $q1 = $em->createQuery($dql1)
            ->setParameter('status1', RelationshipStatusEnumType::FS_ACCEPTED)
            ->setParameter('status2', RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME)
            ->setParameter('user_id', $userId);

        $q2 = $em->createQuery($dql2)
            ->setParameter('role1', RoleCommunityEnumType::RC_OWNER )
            ->setParameter('role2', RoleCommunityEnumType::RC_PARTICIPANT)
            ->setParameter('user_id', $userId);

        $result = array_merge($q1->getResult(), $q2->getResult());
        usort($result,  function($a, $b){ return $a['ts'] < $b['ts']; });

        return $result;
    }

    /**
     * @param $threadId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCommunityByWallThreadId($threadId)
    {
        $em = $this->getEntityManager();
        $dql = "
            SELECT c FROM NetworkStoreBundle:Community c
            JOIN c.wallThreads w
            WHERE w.id = :thread_id";

        $query = $em->createQuery($dql)
                    ->setParameter('thread_id', $threadId);

        $r = $query->getOneOrNullResult();

        return $r;

    }

    /**
     * @param $threadId
     * @return bool
     */
    public function isThreadFromWall($threadId)
    {
        $em = $this->getEntityManager();
        $dql = "
            SELECT u FROM NetworkStoreBundle:User u
            JOIN u.wallThreads uw
            WHERE uw.id = :threadId
        ";

        $query = $em->createQuery($dql)
            ->setParameter('threadId', $threadId);
        if(count($query->getResult()))
            return true;

        $dql = "
            SELECT c FROM NetworkStoreBundle:Community c
            JOIN c.wallThreads wt
            WHERE wt.id = :threadId
        ";

        $query = $em->createQuery($dql)
            ->setParameter('threadId', $threadId);

        return count($query->getResult()) != 0;
    }

    /**
     * @param $threadId
     * @return array
     */
    public function getThreadData($threadId)
    {
        $em = $this->getEntityManager();
        $dql = "
            SELECT p.text, p.ts FROM NetworkStoreBundle:Thread th
            JOIN th.posts p
            WHERE th.id = :threadId
        ";

        $query = $em->createQuery($dql)
                    ->setParameter('threadId', $threadId);

        return $query->getResult();
    }
}
