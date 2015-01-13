<?php
namespace Network\StoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Network\StoreBundle\Entity\Album;

class AlbumRepository extends EntityRepository
{
    /**
     * Get data from $url through cURL.
     *
     * @param string $url
     *
     * @return string
     */
    private function getData($url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        return curl_exec($curl);
    }

    /**
     * Get Album entity by title, year and artist.
     * If doesn't exist, create it.
     *
     * @param string $artist
     * @param string $title
     * @param int $year
     *
     * @return Album
     */
    public function getAlbumByATY($artist, $title, $year)
    {
        $em = $this->getEntityManager();
        $album = $this->findOneBy([
            'title' => $title,
            'year' => $year,
        ]);

        if (null === $album) {
            $album = new Album();

            $format = 'https://itunes.apple.com/search?
                        term=%s
                        &media=music
                        &entity=album
                        &artistTerm=%s
                        &limit=200';

            $apiUrl = preg_replace('/\s+/', '', sprintf($format, urlencode($title), urlencode($artist)));

            $data = $this->getData($apiUrl);

            if ($data) {
                $data = json_decode($data, true);

                foreach ($data['results'] as $result) {
                    $releaseYear = (int) (new \DateTime($result['releaseDate']))->format('Y');

                    if (
                        $releaseYear === $year
                        && $result['artistName'] === $artist
                        && $result['collectionName'] === $title
                    ) {
                        $url = null;

                        if (array_key_exists('artworkUrl100', $result)) {
                            $url = $result['artworkUrl100'];
                        } else if (array_key_exists('artworkUrl60', $result)) {
                            $url = $result['artworkUrl60'];
                        }

                        if (null !== $url) {
                            $album->setPoster($this->getData($url));
                        }

                        break;
                    }
                }
            }

            $album->setTitle($title)
                  ->setYear($year);

            $em->persist($album);
            $em->flush($album);
        }

        return $album;
    }

} 