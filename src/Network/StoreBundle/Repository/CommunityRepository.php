<?php

namespace Network\StoreBundle\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Types\Type;



class CommunityRepository extends EntityRepository
{
    /**
     * @param integer $userId
     * @param integer $communityId
     * @return bool
     */
    public function isUserInCommunity($userId, $communityId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
           ->from('NetworkStoreBundle:UserCommunity', 'r')
           ->where('r.user = :user')
           ->andWhere('r.community = :community')
           ->setParameters(['user' => $userId, 'community' => $communityId]);

        return empty($qb->getQuery()->getResult());
    }
    
    /**
     * @param integer $userId
     * @param integer $communityId
     */
    public function excludeUser($userId, $communityId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete()
           ->from('NetworkStoreBundle:UserCommunity', 'r')
           ->where('r.user = :user')
           ->andWhere('r.community = :community')
           ->setParameters(['user' => $userId, 'community' => $communityId]);

        $qb->getQuery()->getResult();
    }
    
    /**
     * @param integer $communityId
     */
    public function excludeUsers($communityId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete()
           ->from('NetworkStoreBundle:UserCommunity', 'r')
           ->where('r.community = :community')
           ->setParameters(['community' => $communityId]);

        $qb->getQuery()->getResult();
    }
    
    /**
     * @param integer $userId
     * @param integer $communityId
     * @param string $role
     * @return bool
     */
    public function userInCommunityRole($userId, $communityId, $role)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
           ->from('NetworkStoreBundle:UserCommunity', 'r')
           ->where('r.user = :user')
           ->andWhere('r.community = :community')
           ->andWhere('r.role = :role')
           ->setParameters(['user' => $userId, 'community' => $communityId, 'role' => $role]);

        return $qb->getQuery()->getResult();
    }
    
    /**
     * @param integer $id
     * @param string $class
     * @return array
     */
    public function getFindBy($id, $class) {
        return $this->getEntityManager()->getRepository('NetworkStoreBundle:'.$class)->find($id);
    }
    
    /**
     * @param integer $id
     * @param string $role
     * @return array
     */
    public function getUserRole($id, $role) {
        return $this->getEntityManager()->getRepository('NetworkStoreBundle:UserCommunity')->findBy([
                'user' => $id,
                'role' => $role 
            ]);
    }    
    
    /**
     * @param integer $id
     * @param integer $communityId
     * @return array
     */
    public function getUser($id, $communityId) {
        return $this->getEntityManager()->getRepository('NetworkStoreBundle:UserCommunity')->findOneBy([
                'community' => $communityId,
                'user' => $id 
            ]);
    }
    /**
     * @param integer $id
     * @return array
     */
    public function getUsers($id) {
        return $this->getEntityManager()->getRepository('NetworkStoreBundle:UserCommunity')->findBy([
                'community' => $id,
            ]);
    }
}
