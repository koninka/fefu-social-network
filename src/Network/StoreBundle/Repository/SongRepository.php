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
        if (
            !array_key_exists('title', $metadata)
            || !array_key_exists('artist', $metadata)
            || !array_key_exists('genre', $metadata)
        ) {
            return null;
        }

        $song = $this->findOneBy([
            'title' => $metadata['title'][0],
            'artist' => $metadata['artist'][0],
        ]);

        if (null === $song) {
            $song = new Song();

            $song->setTitle($metadata['title'][0])
                 ->setArtist($metadata['artist'][0])
                 ->setGenre($metadata['genre'][0]);

            if (
                array_key_exists('album', $metadata)
                && array_key_exists('year', $metadata)
            ) {
                $album = $this->getEntityManager()
                              ->getRepository('NetworkStoreBundle:Album')
                              ->getAlbumByATY(
                                  $metadata['artist'][0],
                                  $metadata['album'][0],
                                  (int)$metadata['year'][0]
                              );

                $song->setAlbum($album);
            }
        }

        return $song;
    }
}