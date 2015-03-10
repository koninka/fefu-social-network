<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;


/**
 * Blacklist
 *
 * @ORM\Table(name="blacklist")
 * @ORM\Entity
 */
class Blacklist
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
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="User", mappedBy="blacklist")
     */
    private $user;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Thread", cascade={"persist"})
     */
    private $threads;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="User",  mappedBy="id")
     */
    private $blockedUsers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->threads = new ArrayCollection();
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
     * Get user
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param integer $user
     * @return Blacklist
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get threads
     *
     * @return ArrayCollection
     */
    public function getThreads()
    {
        return $this->threads;
    }

    /**
     * Set threads
     *
     * @param ArrayCollection $threads
     * @return Blacklist
     */
    public function setThreads($threads)
    {
        $this->threads = $threads;

        return $this;
    }

    /**
     * Add thread to collection
     *
     * @param Thread $thread
     * @return Blacklist
     */
    public function addThread($thread)
    {
        if(!in_array($thread, $this->threads->toArray()))
            $this->threads->add($thread);

        return $this;
    }

    /**
     * Remove thread from collection
     *
     * @param $thread
     * @return Blacklist
     */
    public function removeThread($thread)
    {
        $this->threads->removeElement($thread);

        return $this;
    }

    /**
     * Get blocked users
     *
     * @return ArrayCollection
     */
    public function getBlockedUsers()
    {
        return $this->blockedUsers;
    }

    /**
     * Set blocked users
     *
     * @param $blockedUsers
     * @return Blacklist
     */
    public function setBlockedUsers($blockedUsers)
    {
        $this->blockedUsers = $blockedUsers;

        return $this;
    }

    /**
     * Add user to collection
     *
     * @param $user
     * @return Blacklist
     */
    public function addBlockedUser($user)
    {
        $this->blockedUsers->add($user);

        return $this;
    }

    /**
     * Remove user from collection
     *
     * @param $user
     * @return Blacklist
     */
    public function removeBlockedUser($user)
    {
        $this->blockedUsers->removeElement($user);

        return $this;
    }
}
