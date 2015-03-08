<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * user_thread
 *
 * @ORM\Table(name="users_threads")
 * @ORM\Entity(repositoryClass="Network\StoreBundle\Repository\UserThreadRepository")
 */
class UserThread
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userThreads")
     */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Thread", inversedBy="userThreads")
     */
    private $thread;

    /**
     * @var integer
     * @ORM\Column(name="unreadPosts", type="integer")
     */
    private $unreadPosts = 0;

    /**
     * @var integer
     * @ORM\Column(name="inviter", type="integer")
     */
    private $inviter;

    /**
     * @param User    $user
     * @param Thread  $thread
     * @param integer $inviter
     */
    public function __construct($user, $thread, $inviter)
    {
        $this->user = $user;
        $this->thread = $thread;
        $this->inviter = $inviter;
        $user->addUserThread($this);
        $thread->addUserThread($this);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * erases current UserThread
     * trying to remove it from database without cycles in user and thread
     */
    public function erase()
    {
        $this->user = null;
        $this->thread = null;
        //$this->user->removeUserThread($this);
        //$this->thread->removeUserThread($this);
    }
    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return integer
     */
    public function getInviter()
    {
        return $this->inviter;
    }
    /**
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @return integer
     */
    public function getUnreadPosts()
    {
        return $this->unreadPosts;
    }

    /**
     * @param integer $unreadPosts
     *
     * @return UserThread
     */
    public function setUnreadPosts($unreadPosts)
    {
        $this->unreadPosts = $unreadPosts;

        return $this;
    }

    /**
     * inc unreadPosts counter
     */
    public function incUnreadPosts()
    {
        ++$this->unreadPosts;
    }

    /**
     * dec unreadPosts counter
     *
     * @param integer $count
     */
    public function decUnreadPosts($count = 1)
    {
        $this->unreadPosts -= $count;
        if ($this->unreadPosts < 0) {
            $this->unreadPosts = 0;
        }
    }

    /**
     * Set inviter
     *
     * @param integer $inviter
     * @return UserThread
     */
    public function setInviter($inviter)
    {
        $this->inviter = $inviter;

        return $this;
    }

    /**
     * Set user
     *
     * @param \Network\StoreBundle\Entity\User $user
     * @return UserThread
     */
    public function setUser(\Network\StoreBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set thread
     *
     * @param \Network\StoreBundle\Entity\Thread $thread
     * @return UserThread
     */
    public function setThread(\Network\StoreBundle\Entity\Thread $thread)
    {
        $this->thread = $thread;

        return $this;
    }
}
