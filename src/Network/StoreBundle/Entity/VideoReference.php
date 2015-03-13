<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Network\StoreBundle\Repository\VideoReferenceRepository")
 * @ORM\Table(name="videoreference")
 */
class VideoReference
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="posts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var Video
     *
     * @ORM\ManyToOne(targetEntity="Video", inversedBy="video")
     * @ORM\JoinColumn(name="video_id", referencedColumnName="id")
     */
    private $video;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(type="string", name="name", length=500)
     */
    private $name;

    /**
     * @var string
     * @Assert\Blank()
     * @ORM\Column(type="string", name="description", length=1500)
     */
    private $description;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @param int $id
     *
     * @return VideoReference
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * @param User $user
     *
     * @return VideoReference
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Video
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @param Video $video
     *
     * @return VideoReference
     */
    public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return VideoReference
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $description
     *
     * @return VideoReference
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
