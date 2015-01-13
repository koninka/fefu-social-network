<?php
namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="mp3s")
 */
class MP3Record
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
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=300)
     */
    private $path;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="uploaded", type="datetime")
     */
    private $uploaded;

    /**
     * @var Song
     *
     * @ORM\ManyToOne(targetEntity="Song", cascade={"persist"})
     * @ORM\JoinColumn(name="song_id", referencedColumnName="id")
     */
    private $song;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="User", mappedBy="mp3s")
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return MP3Record
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUploaded()
    {
        return $this->uploaded;
    }

    /**
     * @param \DateTime $uploaded
     *
     * @return MP3Record
     */
    public function setUploaded(\DateTime $uploaded)
    {
        $this->uploaded = $uploaded;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return MP3Record
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Song
     */
    public function getSong()
    {
        return $this->song;
    }

    /**
     * @param Song $song
     *
     * @return MP3Record
     */
    public function setSong($song)
    {
        $this->song = $song;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param ArrayCollection $users
     *
     * @return MP3Record
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    public function addUser(User $user)
    {
        $this->users->add($user);
    }

    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }
}