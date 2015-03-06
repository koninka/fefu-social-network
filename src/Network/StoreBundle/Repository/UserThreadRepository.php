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

    /**
     * @param integer $userId
     *
     * @return integer
     */
    public function getThreadsUnreadForUserCount($userId)
    {
        $predis = new \Snc\RedisBundle\Doctrine\Cache\RedisCache();
        $predis->setRedis(new \Predis\Client());
        $em = $this->getEntityManager();
        $dql = "
            SELECT COUNT(ut) FROM NetworkStoreBundle:UserThread ut
            WHERE ut.user = :user_id and ut.unreadPosts > 0
        ";
        $query = $em->createQuery($dql)
            ->setParameter('user_id', $userId);

        return $query->setResultCacheDriver($predis)
            ->setResultCacheLifetime(1000)
            ->getSingleScalarResult();
    }

    /**
     * @param integer $threadId
     * @param integer $userId
     *
     * @return array
     */
    public function getInvitedUsersByUserInThread($threadId, $userId)
    {
        $em = $this->getEntityManager();
        $dql = "
            SELECT ut FROM NetworkStoreBundle:UserThread ut
            WHERE ut.inviter = :user_id and ut.thread = :thread_id
        ";
        $query = $em->createQuery($dql)
            ->setParameter('user_id', $userId)
            ->setParameter('thread_id', $threadId);
        $r = $query->getArrayResult();
        $rr = array_column($r, 'user_id');

        return $rr;
    }

    /**
     * @param integer $threadId
     * @param integer $userId
     * @param integer $challengerId
     *
     * @return UserThread | null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getChallengerIfCanBeKickedByUserFromThread($threadId, $userId, $challengerId)
    {
        if ($this->getThreadOwnerId($threadId) == $userId) {
            return $this->findByUserAndThread($challengerId, $threadId);
        }
        $em = $this->getEntityManager();
        $dql = "
            SELECT ut FROM NetworkStoreBundle:UserThread ut
            WHERE ut.inviter = :user_id and ut.thread = :thread_id and ut.user = :challenger_id
        ";
        $query = $em->createQuery($dql)
            ->setParameter('user_id', $userId)
            ->setParameter('thread_id', $threadId)
            ->setParameter('challenger_id', $challengerId);

        return $query->getOneOrNullResult();
    }
    /**
     * @param integer $threadId
     *
     * @return integer
     */
    public function getThreadOwnerId($threadId)
    {
        $em = $this->getEntityManager();
        $dql = "
            SELECT ut.inviter as id FROM NetworkStoreBundle:UserThread ut
            WHERE ut.user = ut.inviter AND ut.thread = :thread_id
        ";
        $query = $em->createQuery($dql)
            ->setParameter('thread_id', $threadId);

        return $query->getSingleScalarResult();
    }
}
