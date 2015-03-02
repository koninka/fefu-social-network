<?php

namespace Network\StoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class VideoReferenceRepository extends EntityRepository
{
    public function findByVideoReferenceName($name)
    {
        return $this->getEntityManager()
            ->createQueryBuilder('v')
            ->select('v')
            ->from('NetworkStoreBundle:VideoReference', 'v')
            ->where("v.name LIKE ?1")
            ->setMaxResults(100)
            ->setParameter(1, $name . '%')
            ->getQuery()
            ->getResult();
    }
}
