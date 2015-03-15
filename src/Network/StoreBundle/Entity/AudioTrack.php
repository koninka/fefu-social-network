<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity(repositoryClass="Network\StoreBundle\Repository\AudioTrackRepository")
 * @ORM\Table(name="audio_tracks")
 * @ExclusionPolicy("none")
 */
class AudioTrack
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="uploadedTracks")
     * @Exclude
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="upload_date", type="datetime")
     */
    private $uploadDate;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="artist", type="string", length=255)
     */
    private $artist;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="file_hash", length=64)
     */
    private $fileHash;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="PlaylistItem", mappedBy="audioTrack", cascade={"persist"})
     * @Exclude
     **/
    private $playlistItems;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->playlistItems = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set uploadDate
     *
     * @param \DateTime $uploadDate
     * @return AudioTrack
     */
    public function setUploadDate($uploadDate)
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    /**
     * Get uploadDate
     *
     * @return \DateTime
     */
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return AudioTrack
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set artist
     *
     * @param string $artist
     * @return AudioTrack
     */
    public function setArtist($artist)
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Get artist
     *
     * @return string
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * Set user
     *
     * @param \Network\StoreBundle\Entity\User $user
     * @return AudioTrack
     */
    public function setUser(\Network\StoreBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get User
     *
     * @return \Network\StoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set fileHash
     *
     * @param string $fileHash
     * @return AudioTrack
     */
    public function setFileHash($fileHash)
    {
        $this->fileHash = $fileHash;

        return $this;
    }

    /**
     * Get fileHash
     *
     * @return string
     */
    public function getFileHash()
    {
        return $this->fileHash;
    }

    /**
     * Add playlistItems
     *
     * @param \Network\StoreBundle\Entity\PlaylistItem $playlistItems
     * @return AudioTrack
     */
    public function addPlaylistItem(\Network\StoreBundle\Entity\PlaylistItem $playlistItem)
    {
        $playlistItem->setAudioTrack($this);
        $this->playlistItems[] = $playlistItem;

        return $this;
    }

    /**
     * Remove playlistItems
     *
     * @param \Network\StoreBundle\Entity\PlaylistItem $playlistItems
     */
    public function removePlaylistItem(\Network\StoreBundle\Entity\PlaylistItem $playlistItems)
    {
        $this->playlistItems->removeElement($playlistItems);
    }

    /**
     * Get playlistItems
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlaylistItems()
    {
        return $this->playlistItems;
    }
}
