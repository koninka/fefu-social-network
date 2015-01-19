<?php

namespace Network\StoreBundle\Repository;


use Doctrine\ORM\EntityRepository;

/**
* UserThreadRepository
*
*/
class UserThreadRepository extends EntityRepository
{

    /**
     * @param integer $userId
     * @param integer $threadId
     *
     * @return array
     */
    public function findByUserAndThread($userId, $threadId)
    {
        $em = $this->getEntityManager();
        $dql = "
            SELECT ut from NetworkStoreBundle:UserThread ut
            WHERE ut.user = :user_id AND ut.thread = :thread_id
        ";

        $query = $em->createQuery($dql)
            ->setParameter('user_id', $userId)
            ->setParameter('thread_id', $threadId);

        return $query->getSingleResult();
    }

    public function getThreadsUnreadForUserCount($userId)
    {
        $em = $this->getEntityManager();
        $dql = "
            SELECT COUNT(ut) FROM NetworkStoreBundle:UserThread ut
            WHERE ut.user = :user_id and ut.unreadPosts > 0
        ";
        $query = $em->createQuery($dql)
            ->setParameter('user_id', $userId);

        return $query->getSingleScalarResult();
    }
}
