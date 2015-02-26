<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 23.02.2015
 * Time: 17:27
 */

namespace Network\ImportBundle\Service;


use Application\Sonata\MediaBundle\Entity\Gallery;
use Application\Sonata\MediaBundle\Entity\GalleryHasMedia;
use Application\Sonata\MediaBundle\Entity\Media;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Network\StoreBundle\Entity\UserGallery;
use Symfony\Component\HttpFoundation\File\File;

class ContentLoader {
    protected $container;

    protected static $tables = ['InstagramItem', 'VkontakteItem'];
    private static $loadedStatus = 1;
    private static $notLoaded = 0;
    private static  $userGalleryMap = array();
    private static $idUserMap = array();

    function __construct($container)
    {
        $this->container = $container;
    }

    private function countPages($table, $limit)
    {
        $em = $this->container ->get('doctrine') ->getManager();
        $res = 0;
        $qb = $em->createQueryBuilder('t')
            ->select('count(t.id)')->from('NetworkStoreBundle:'.$table, 't')
            ->andWhere('t.status != 1');
        $count = $qb->getQuery()->getSingleScalarResult();
        if (!$count) {
            return 0;
        }
        if ($count % $limit == 0) {
            $res =  round($count / $limit);
        } else {
            $res =  round($count / $limit) + 1;
        }

        return $res;
    }

    private function paginate($dql, $pageSize = 10, $currentPage = 1)
    {
        $paginator = new Paginator($dql);

        $paginator
            ->getQuery()
            ->setFirstResult($pageSize * ($currentPage - 1)) // set the offset
            ->setMaxResults($pageSize); // set the limit

        return $paginator;
    }

    private function getItemContent($item)
    {
        $em = $this->container ->get('doctrine') ->getManager();
        $media = new Media();
        if (!method_exists($item, 'getUrl')) {
            throw new Exception('UnknownItemException');
        }
        $content = file_get_contents($item->getUrl());
        $name = /*__DIR__
                . '/../../../../web/'
                . 'imports'
                . '/'.*/ 'temp'.rand();
        $stream = fopen($name, 'w');
        fwrite($stream, $content);
        $file = new File($name);
        if (!method_exists($item, 'getType')) {
            throw new Exception('UnknownItemException');
        }
        if ($item->getType() == 'image') {
            $owner = $item->getOwnerId();
            if (!isset(self::$userGalleryMap[$owner])) {
                $resourceOwner = $item->getResourceOwner();
                $query = $em->createQueryBuilder()->select('u')->from('NetworkStoreBundle:User', 'u')->
                        andWhere('u.'.$resourceOwner.'Id = :id')->setParameter('id', $owner)->getQuery();
                $results = $query->getResult();
                if (!empty($results)) {
                    $user = $results[0];
                }
                $galleries = $user->getAlbums()->takeSnapshot();
                if ($galleries) {
                    foreach ($galleries as $g) {
                        if ($g->getGallery()->getName() == $item->getAlbum()) {
                            $gallery = $g;
                            break;
                        }
                    }
                } else {
                    $gallery = new Gallery();
                    $gallery->setName($item->getAlbum())
                        ->setContext('default')
                        ->setDefaultFormat('default_small')
                        ->setEnabled(true);
                    $userAlbum = new UserGallery();
                    $userAlbum->setOwner($user)
                              ->setGallery($gallery);
                    $em->persist($gallery);
                    $em->persist($userAlbum);
                    $em->flush();
                    $user->addAlbum($userAlbum);
                    self::$idUserMap[$owner] = $user;
                    $userManager = $this->container->get('fos_user.user_manager');
                    $userManager->updateUser($user);
                    self::$userGalleryMap[$owner] = $gallery;
                }
            }
            $gallery = self::$userGalleryMap[$owner];
            $media->setBinaryContent($file);
            $media->setContext('default');
            $media->setProviderName('sonata.media.provider.image');
            $mediaManager = $this->container->get('sonata.media.manager.media');
            $mediaManager->save($media);
            $ghm = new GalleryHasMedia();
            $ghm->setGallery($gallery)
                ->setMedia($media);
            $em->persist($ghm);
            $em->flush();
            $gallery->addGalleryHasMedia($ghm);
        }
    }

    public function loadContent()
    {
        $em = $this->container ->get('doctrine') ->getManager();
        $pageSize = 30;
        foreach (self::$tables as $table) {
            if (class_exists('Network\\StoreBundle\\Entity\\'.$table)) {
                $query = $em->createQueryBuilder()->select('t')->from('NetworkStoreBundle:'.$table, 't')->andWhere('t.status != 1');;
                $pages= self::countPages($table, $pageSize);
                $i = 1;
                while ($i <= $pages) {
                    $page = self::paginate($query, $pageSize, $i);
                    $items = $page->getQuery()->getResult();
                    foreach ($items as $item) {
                        //TODO install message queue server
                        self::getItemContent($item);
                        $item->setStatus(self::$loadedStatus);
                    }
                    $em->flush();
                    $em->clear();
                    ++$i;
                }
            }
        }
    }

}
