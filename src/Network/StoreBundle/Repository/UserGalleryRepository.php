<?php

namespace Network\StoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserGalleryRepository extends EntityRepository
{

    /**
     * @param integer $userId
     * @return array
     */
    public function findAlbumsForUser($userId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
           ->from('NetworkStoreBundle:UserGallery', 'g')
           ->where('g.owner = :owner')
           ->setParameters(['owner' => $userId]);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param integer $userId
     * @param integer $galleryId
     * @return array
     */
    public function findAlbumForUser($userId, $galleryId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
           ->from('NetworkStoreBundle:UserGallery', 'g')
           ->where('g.owner = :owner')
           ->andWhere('g.gallery = :gallery')
           ->setParameters(['owner' => $userId, 'gallery' => $galleryId]);

        return $qb->getQuery()->getResult() ? $qb->getQuery()->getResult()[0] : null;
    }

    /**
     * @param integer $userId
     * @param integer $galleryId
     * @return bool
     */
    public function isUserAlbum($userId, $galleryId)
    {
        return $this->findAlbumForUser($userId, $galleryId) != null;
    }

}
