<?php

namespace Network\StoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;

class RelationshipRepository extends EntityRepository
{

    /**
     * @param integer $userId
     * @param string $relationshipStatus
     * @return array
     */
    private function findRelationshipsForUser($userId, $relationshipStatus)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
           ->from('NetworkStoreBundle:Relationship', 'r')
           ->where('r.user = :user')
           ->andWhere('r.status = :status')
           ->setParameters(['user' => $userId, 'status' => $relationshipStatus]);

        return $qb->getQuery()->getResult();
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
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
           ->from('NetworkStoreBundle:Relationship', 'r')
           ->where('r.user = :user')
           ->andWhere('r.hidden = false')
           ->andWhere('r.status = :status')
           ->setParameters(['user' => $userId, 'status' => RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER]);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param integer $userId
     * @return integer
     */
    public function getFriendshipRequestsForUserCount($userId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(r.id)')
           ->from('NetworkStoreBundle:Relationship', 'r')
           ->where('r.user = :user')
           ->andWhere('r.hidden = false')
           ->andWhere('r.status = :status')
           ->setParameters(['user' => $userId, 'status' => RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER]);

        return $qb->getQuery()->getSingleScalarResult();
    }

}
