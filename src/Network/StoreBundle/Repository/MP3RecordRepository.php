<?php
namespace Network\StoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Network\StoreBundle\Repository\SongRepository;
use Network\StoreBundle\Entity\Song;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Query\Expr\Join;

class MP3RecordRepository extends EntityRepository
{
    public function searchRecords($by, $what)
    {
        return $this->getEntityManager()
                    ->createQueryBuilder('m')
                    ->select('m')
                    ->from('NetworkStoreBundle:MP3Record', 'm')
                    ->innerJoin('NetworkStoreBundle:Song', 's', Join::WITH, 'm.song = s.id')
                    ->where("s.$by LIKE ?1")
                    ->setMaxResults(50)
                    ->setParameter(1, $what . '%')
                    ->getQuery()
                    ->getResult();
    }
}