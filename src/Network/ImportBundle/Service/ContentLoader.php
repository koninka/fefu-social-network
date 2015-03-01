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
use Network\StoreBundle\Entity\MP3File;
use Network\StoreBundle\Entity\MP3Record;
use Network\StoreBundle\Entity\UserGallery;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Console\Output\OutputInterface;

class ContentLoader extends PageRequestor {
    protected $container;

    protected static $tables = ['InstagramItem', 'VkontakteItem', 'FacebookItem'];
    private static $loadedStatus = 1;
    private static  $userGalleryMap = array();
    private static $idUserMap = array();
    const PAGE_SIZE = 50;
    const IMPORT_DIR = '';//__DIR__ . '/../../../../web/' . 'imports' . '/';

    function __construct($container)
    {
        $this->container = $container;
    }

    public function getItemContent($item)
    {
        $resourceOwner = $item->getResourceOwner();
        $owner = $item->getOwnerId();
        $em = $this->container->get('doctrine')->getManager();
        $media = new Media();
        if (!method_exists($item, 'getUrl')) {
            throw new Exception('UnknownItemException');
        }
        $content = file_get_contents($item->getUrl());
        $name = static::IMPORT_DIR . 'temp' . uniqid();
        $stream = fopen($name, 'w');
        fwrite($stream, $content);
        $file = new File($name);
        if (!method_exists($item, 'getType')) {
            throw new Exception('UnknownItemException');
        }
        if ($item->getType() == 'image') {
            if (!isset(self::$userGalleryMap[$owner])) {
                $query = $em->createQueryBuilder()
                    ->select('u')
                    ->from('NetworkStoreBundle:User', 'u')
                    ->andWhere('u.' . $resourceOwner . 'Id = :id')
                    ->setParameter('id', $owner)
                    ->getQuery();
                $results = $query->getResult();
                if (empty($results)) {
                    return;
                }
                $user = $results[0];
                $galleries = $this->container
                    ->get('doctrine')
                    ->getRepository('NetworkStoreBundle:UserGallery')
                    ->findAlbumsForUser($user->getId());
                if (!empty($galleries)) {
                    foreach ($galleries as $g) {
                        if ($g->getGallery()->getName() == $item->getAlbum()) {
                            $gallery = $g;
                            self::$userGalleryMap[$owner] = $gallery->getGallery();
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
                    //$em->flush();
                    $user->addAlbum($userAlbum);
                    self::$idUserMap[$owner] = $user;
                    $userManager = $this->container->get('fos_user.user_manager');
                    $userManager->updateUser($user);
                    self::$userGalleryMap[$owner] = $gallery;
                }
            }
            if (!isset(self::$userGalleryMap[$owner])) {
                return;
            }
            $gallery = self::$userGalleryMap[$owner];
            $media->setBinaryContent($file);
            $media->setContext('default');
            $media->setProviderName('sonata.media.provider.image');
            $mediaManager = $this->container
                                 ->get('sonata.media.manager.media');
            $mediaManager->save($media);
            $ghm = new GalleryHasMedia();
            $ghm->setGallery($gallery)
                ->setMedia($media);
            $em->persist($ghm);
            $gallery->addGalleryHasMedia($ghm);
        } else if ('audio' === $item->getType()) {
            $query = $em->createQueryBuilder()
                        ->select('u')
                        ->from('NetworkStoreBundle:User', 'u')
                        ->andWhere('u.' . $resourceOwner . 'Id = :id')
                        ->setParameter('id', $owner)
                        ->getQuery();
            $results = $query->getResult();
            if (!empty($results)) {
                if (!method_exists($item, 'getTitle')
                    || !method_exists($item, 'getArtist')
                    || !method_exists($item, 'getGenre')) {
                    throw new Exception('UnknownItemException');
                }
                $metadata['title'] = $item->getTitle();
                $metadata['artist'] = $item->getArtist();
                $metadata['genre'] = $item->getGenre() !== null ? $item->getGenre() : "none";
                $song = $this->container
                             ->get('doctrine')
                             ->getRepository('NetworkStoreBundle:Song')
                             ->getSongByMetadata($metadata);
                $user = $results[0];
                $mp3 = new MP3File();
                $mp3->setPath($file->getRealPath());
                $em->persist($mp3);
                $record = new MP3Record();
                $record->addUser($user);
                $record->setFile($mp3)
                       ->setUploaded(new \DateTime())
                       ->setSong($song);
                $em->persist($record);
            }

        }
        $item->setStatus(self::$loadedStatus);
}

    public function loadContent()
    {
        $em = $this->container->get('doctrine')->getManager();
        foreach (self::$tables as $table) {
            if (class_exists('Network\\StoreBundle\\Entity\\' . $table)) {
                $query = $em->createQueryBuilder()
                            ->select('t')
                            ->from('NetworkStoreBundle:' . $table, 't')
                            ->andWhere('t.status != 1');
                $countQuery = $em->createQueryBuilder('t')
                                 ->select('count(t.id)')
                                 ->from('NetworkStoreBundle:' . $table, 't')
                                 ->andWhere('t.status != 1');
                $pages= self::countPages($countQuery, static::PAGE_SIZE);
                for ($i = 1; $i <= $pages; ++$i) {
                    $page = self::paginate($query, static::PAGE_SIZE, $i);
                    $itemsDQL = $page->getQuery()->getDQL();
                    $this->container
                         ->get('old_sound_rabbit_mq.fetch_content_producer')
                         ->publish($itemsDQL);
                }
                if ($pages) {
                    self::clearMetaInf($table);
                }
            }
        }
    }

    public function clearMetaInf($table)
    {
        $em = $this->container ->get('doctrine')->getManager();
        if (class_exists('Network\\StoreBundle\\Entity\\' . $table)) {
            $em->createQueryBuilder()
                ->delete()
                ->from('NetworkStoreBundle:' . $table, 't')
                ->andWhere('t.status = 1')
                ->getQuery()
                ->execute();
        }
    }

}
