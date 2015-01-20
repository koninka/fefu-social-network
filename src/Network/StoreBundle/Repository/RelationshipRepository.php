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
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('NetworkStoreBundle:Relationship', 'r')
            ->where('r.user = :user')
            ->andWhere('r.status = :status')
            ->setParameters(['user' => $userId, 'status' => $relationshipStatus]);

        return $qb->getQuery();
    }

    /**
     * @param integer   $userId
     * @param Paginator $paginator
     * @param integer   $page
     * @param integer   $limit
     * @param string    $q
     *
     * @return array
     */
    public function getPaginatedFriends($userId, $paginator, $page, $limit, $q)
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
            ->setParameter('q', '%'. $q .'%')
            ->getQuery();
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
