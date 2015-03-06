<?php

namespace Network\StoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Network\StoreBundle\Entity\Relationship;
use Network\StoreBundle\Service\Paginator;

class RelationshipRepository extends EntityRepository
{

    /**
     * @param $userId
     * @param $partnerId
     * @return Relationship
     */
    public function getRelationshipForUser($userId, $partnerId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('NetworkStoreBundle:Relationship', 'r')
            ->where('r.user = :user')
            ->andWhere('r.partner = :partner')
            ->setParameters(['user' => $userId, 'partner' => $partnerId]);

        $relationship = $qb->getQuery()->getOneOrNullResult();
        if (!$relationship) {
            $relationship = new Relationship();
        }

        return $relationship;
    }

    /**
     * @param integer $userId
     * @param string  $relationshipStatus
     *
     * @return Query
     */
    private function getQueryFindRelationshipForUser($userId, $relationshipStatus)
    {
       /* $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('NetworkStoreBundle:Relationship', 'r')
            ->where('r.user = :user')
            ->andWhere('r.status = :status')
            ->setParameters(['user' => $userId, 'status' => $relationshipStatus]);

        return $qb->getQuery();*/
        $em = $this->getEntityManager();
        $dql = "
            SELECT r, p com from NetworkStoreBundle:Relationship r
            JOIN r.partner p
            WHERE r.user = :user
            AND r.status = :status
        ";
        $query = $em->createQuery($dql)
            ->setParameters(['user' => $userId, 'status' => $relationshipStatus]);

        return $query;
    }

    /**
     * @param integer   $userId
     * @param Paginator $paginator
     * @param integer   $page
     * @param integer   $limit
     * @param string    $q
     * @param           $filter
     *
     * @return array
     */
    public function getPaginatedAndFilteredFriends($userId, $paginator, $page, $limit, $q, $filter)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('r')
            ->from('NetworkStoreBundle:Relationship', 'r')
            ->join('r.user', 'u')
            ->join('r.partner', 'p')
            ->where('u.id = :user')
            ->andWhere('CONCAT(p.firstName, CONCAT(\' \', p.lastName)) LIKE :q')
            ->andWhere('r.status = :status')
            ->setParameters(['user' => $userId, 'status' => RelationshipStatusEnumType::FS_ACCEPTED])
            ->setParameter('q', '%'. $q .'%');
        if ($filter != null) {
            $query = $filter($query);
        }
        $query = $query->getQuery();
        $format = function($friend) {
            $p = $friend->getPartner();

            return ['id' => $p->getId(), 'text' => $p->getFirstName() . ' ' . $p->getLastName()];
        };

       return $paginator->getPaginatedResult($query, $page, $limit, $format);
    }

    /**
     * @param integer $userId
     * @param string $relationshipStatus
     * @return array
     */
    private function findRelationshipsForUser($userId, $relationshipStatus)
    {
        return $this->getQueryFindRelationshipForUser($userId, $relationshipStatus)->getResult();
    }

    /**
     * @param integer $userId
     * @return array
     */
    public function findFriendsForUser($userId)
    {
        return $this->findRelationshipsForUser($userId, RelationshipStatusEnumType::FS_ACCEPTED);
    }

    /**
     * @param integer $userId
     * @return array
     */
    public function findSubscribersForUser($userId)
    {
        return $this->findRelationshipsForUser($userId, RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER);
    }

    /**
     * @param integer $userId
     * @return array
     */
    public function findSubscribedOnForUser($userId)
    {
        return $this->findRelationshipsForUser($userId, RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
    }

    /**
     * @param integer $userId
     * @return array
     */
    public function findFriendshipRequestsForUser($userId)
    {
        $predis = new \Snc\RedisBundle\Doctrine\Cache\RedisCache();
        $predis->setRedis(new \Predis\Client());
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
           ->from('NetworkStoreBundle:Relationship', 'r')
           ->where('r.user = :user')
           ->andWhere('r.hidden = false')
           ->andWhere('r.status = :status')
           ->setParameters(['user' => $userId, 'status' => RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER]);

        return $qb->getQuery()
            ->setResultCacheDriver($predis)
            ->setResultCacheLifetime(1000)
            ->getResult();
    }

    /**
     * @param integer $userId
     * @return integer
     */
    public function getFriendshipRequestsForUserCount($userId)
    {
        $predis = new \Snc\RedisBundle\Doctrine\Cache\RedisCache();
        $predis->setRedis(new \Predis\Client());
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(r.id)')
           ->from('NetworkStoreBundle:Relationship', 'r')
           ->where('r.user = :user')
           ->andWhere('r.hidden = false')
           ->andWhere('r.status = :status')
           ->setParameters(['user' => $userId, 'status' => RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER]);

        return $qb->getQuery()->setResultCacheDriver($predis)
            ->setResultCacheLifetime(1000)->getSingleScalarResult();
    }

}
