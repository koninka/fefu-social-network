<?php

namespace Network\StoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Network\StoreBundle\Entity\AudioTrack;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class AudioTrackRepository extends EntityRepository
{
    public function searchRecords($by, $what)
    {
        return $this->getEntityManager()
                    ->createQueryBuilder('m')
                    ->select('m')
                    ->from('NetworkStoreBundle:AudioTrack', 'm')
                    ->where("s.$by LIKE ?1")
                    ->setMaxResults(50)
                    ->setParameter(1, $what . '%')
                    ->getQuery()
                    ->getResult();
    }
}