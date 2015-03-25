<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Network\StoreBundle\DBAL\TypePostEnumType;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="Network\StoreBundle\Repository\PostRepository")
 */
class Post
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
     * @var \DateTime
     *
     * @ORM\Column(name="ts", type="datetime")
     */
    private $ts;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="typePostEnumType")
     */
    private $type;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="User", cascade="persist")
     * @ORM\JoinTable(name="post_like",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id")}
     * )
     */
    private $likes;


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="PostFile", mappedBy="post", cascade={"persist"})
     */
    protected $postFiles;

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
     * Set ts
     *
     * @param \DateTime $ts
     * @return Post
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts
     *
     * @return \DateTime
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Post
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="posts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * Set user
     *
     * @param \Network\StoreBundle\Entity\User $user
     * @return Post
     */
    public function setUser(\Network\StoreBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Network\StoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Thread", inversedBy="posts")
     * @ORM\JoinColumn(name="thread_id", referencedColumnName="id")
     */
    protected $thread;

    /**
     * Set thread
     *
     * @param \Network\StoreBundle\Entity\Thread $thread
     * @return Post
     */
    public function setThread(\Network\StoreBundle\Entity\Thread $thread = null)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Get thread
     *
     * @return \Network\StoreBundle\Entity\Thread
     */
    public function getThread()
    {
        return $this->thread;
    }
    
     /**
     * Set type
     *
     * @param string $type
     * @return \Network\StoreBundle\Entity\Post
     */
    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->type = TypePostEnumType::TP_TEXT;
        $this->postFiles = new ArrayCollection();
    }

    /**
     * Add likes
     *
     * @param \Network\StoreBundle\Entity\User $likes
     * @return Post
     */
    public function addLike(\Network\StoreBundle\Entity\User $likes)
    {
        $this->likes[] = $likes;
        
        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

     /**
     * Remove likes
     *
     * @param \Network\StoreBundle\Entity\User $likes
     */
    public function removeLike(\Network\StoreBundle\Entity\User $likes)
    {
        $this->likes->removeElement($likes);
    }

    /**
     * Get likes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * Add file
     *
     * @param \Network\StoreBundle\Entity\PostFile $file
     * @return Post
     */
    public function addFile(\Network\StoreBundle\Entity\PostFile $file )
    {
        $this->postFiles[] = $file;

        return $this;
    }

    /**
     * Remove file
     *
     * @param \Network\StoreBundle\Entity\PostFile $file
     */
    public function removeFile(\Network\StoreBundle\Entity\PostFile $file)
    {
        $this->postFiles->removeElement($file);
    }

    /**
     * Get likes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->postFiles;
    }

    public function __toString()
    {
        return null;
    }
}
