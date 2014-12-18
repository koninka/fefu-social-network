<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;

/**
 * Relationship
 *
 * @ORM\Table(name="relationships")
 * @ORM\Entity(repositoryClass="Network\StoreBundle\Repository\RelationshipRepository")
 */
class Relationship
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
     * @ORM\JoinColumn(name="partner_id", referencedColumnName="id")
     */
    protected $partner;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="relationshipStatusEnumType")
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hidden", type="boolean")
     */
    private $hidden;

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
     * @return \Network\StoreBundle\Entity\Relationship
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
     * Set partner
     *
     * @param \Network\StoreBundle\Entity\User $partner
     * @return \Network\StoreBundle\Entity\Relationship
     */
    public function setPartner(\Network\StoreBundle\Entity\User $partner = null)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * Get partner
     *
     * @return \Network\StoreBundle\Entity\User 
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return \Network\StoreBundle\Entity\Relationship
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param boolean $hidden
     * @return \Network\StoreBundle\Entity\Relationship
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = RelationshipStatusEnumType::FS_NONE;
        $this->hidden = false;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->getPartner()->getFirstName() . ' ' . $this->getPartner()->getLastName() . ': ' . $this->getStatus();
    }

}
