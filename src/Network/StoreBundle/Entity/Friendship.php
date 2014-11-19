<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Network\StoreBundle\DBAL\FriendshipStatusEnumType;

/**
 * Friendship
 *
 * @ORM\Table(name="user_friend", uniqueConstraints={@ORM\UniqueConstraint(name="friendship_idx", columns={"user_id", "friend_id"})})
 * @ORM\Entity
 */

class Friendship
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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="friend_id", referencedColumnName="id")
     */

    protected $friend;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="friendshipStatusEnumType")
     * @Assert\NotBlank()
     */
    private $status;

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
     * Set user
     *
     * @param \Network\StoreBundle\Entity\User $user
     * @return Friendship
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
     * Set friend
     *
     * @param \Network\StoreBundle\Entity\User $friend
     * @return Friendship
     */
    public function setFriend(\Network\StoreBundle\Entity\User $friend = null)
    {
        $this->friend = $friend;

        return $this;
    }

    /**
     * Get friend
     *
     * @return \Network\StoreBundle\Entity\User 
     */
    public function getFriend()
    {
        return $this->friend;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = FriendshipStatusEnumType::FS_REQUESTED;
    }

    public function __toString()
    {
        return $this->friend->getName();
    }

}
