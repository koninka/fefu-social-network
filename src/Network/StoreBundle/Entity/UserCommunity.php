<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Network\StoreBundle\DBAL\RoleCommunityEnumType;

/**
 * User_Community
 *
 * @ORM\Table(name="user_community")
 * @ORM\Entity
 */
class UserCommunity
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
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Community")
     * @ORM\JoinColumn(name="community_id", referencedColumnName="id")
     */
    private $community;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="roleCommunityEnumType")
     * @Assert\NotBlank()
     */
    private $role;

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
     * Set role
     *
     * @param string $role
     * @return User_Community
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set user
     *
     * @param \Network\StoreBundle\Entity\User $user
     * @return User_Community
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
     * Set community
     *
     * @param \Network\StoreBundle\Entity\Community $community
     * @return User_Community
     */
    public function setCommunity(\Network\StoreBundle\Entity\Community $community = null)
    {
        $this->community = $community;

        return $this;
    }

    /**
     * Get community
     *
     * @return \Network\StoreBundle\Entity\Community 
     */
    public function getCommunity()
    {
        return $this->community;
    }
    
    public function __toString()
    {
        return $this->getCommunity()->getName();
    }
}
