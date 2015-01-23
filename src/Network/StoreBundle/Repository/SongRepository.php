<?php
namespace Network\StoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Network\StoreBundle\Repository\AlbumRepository;
use Network\StoreBundle\Entity\Song;

class SongRepository extends EntityRepository
{
    /**
     * Return Song entity related to specified metadata.
     * If doesn't exist, create it.
     *
     * @param array $metadata
     *
     * @return Song
     */
    public function getSongByMetadata(array $metadata)
    {
        $song = $this->findOneBy([
            'title' => $metadata['title'],
            'artist' => $metadata['artist'],
            'genre' => $metadata['genre'],
        ]);

        if (null === $song) {
            $song = new Song();

            $song->setTitle($metadata['title'])
                 ->setArtist($metadata['artist'])
                 ->setGenre($metadata['genre']);

            if (
                array_key_exists('album', $metadata)
                && array_key_exists('year', $metadata)
            ) {
                $album = $this->getEntityManager()
                              ->getRepository('NetworkStoreBundle:Album')
                              ->getAlbumByATY(
                                  $metadata['artist'],
                                  $metadata['album'],
                                  (int)$metadata['year']
                              );

                $song->setAlbum($album);
            }
        }

        return $song;
    }
}