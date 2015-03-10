<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlaylistItem
 *
 * @ORM\Table(name="playlist_items")
 * @ORM\Entity(repositoryClass="Network\StoreBundle\Repository\PlaylistItemRepository")
 */
class PlaylistItem
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Playlist", inversedBy="items")
     */
    private $playlist;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AudioTrack", inversedBy="playlistItems")
     */
    private $audioTrack;

    /**
     * @var integer
     *
     * @ORM\Column(name="rank", type="integer")
     */
    private $rank;

    /**
     * Set rank
     *
     * @param integer $rank
     * @return PlaylistItem
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return integer
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set playlist
     *
     * @param \Network\StoreBundle\Entity\Playlist $playlist
     * @return PlaylistItem
     */
    public function setPlaylist(\Network\StoreBundle\Entity\Playlist $playlist)
    {
        $this->playlist = $playlist;

        return $this;
    }

    /**
     * Get playlist
     *
     * @return \Network\StoreBundle\Entity\Playlist
     */
    public function getPlaylist()
    {
        return $this->playlist;
    }

    /**
     * Set audioTrack
     *
     * @param \Network\StoreBundle\Entity\AudioTrack $audioTrack
     * @return PlaylistItem
     */
    public function setAudioTrack(\Network\StoreBundle\Entity\AudioTrack $audioTrack)
    {
        $this->audioTrack = $audioTrack;

        return $this;
    }

    /**
     * Get audioTrack
     *
     * @return \Network\StoreBundle\Entity\AudioTrack
     */
    public function getAudioTrack()
    {
        return $this->audioTrack;
    }
}
