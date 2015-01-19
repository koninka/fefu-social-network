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
     * @param User   $user
     * @param Thread $thread
     */
    public function __construct($user, $thread)
    {
        $this->user = $user;
        $this->thread = $thread;
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
}
