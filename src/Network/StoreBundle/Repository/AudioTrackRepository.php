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
                    ->createQueryBuilder()
                    ->select('t')
                    ->from('NetworkStoreBundle:AudioTrack', 't')
                    ->where("t.$by LIKE ?1")
                    ->setMaxResults(50)
                    ->setParameter(1, $what . '%')
                    ->getQuery()
                    ->getResult();
    }
}