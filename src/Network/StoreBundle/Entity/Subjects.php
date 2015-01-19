<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subjects
 *
 * @ORM\Table(name="subjects")
 * @ORM\Entity
 */
class Subjects
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    /**
     * @ORM\OneToMany(targetEntity="Community", mappedBy="subjects", cascade={"persist"}, orphanRemoval=true)
     */
    private $community;

    public function __construct()
    {
        $this->community = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Subjects
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
   
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Add community
     *
     * @param \Network\StoreBundle\Entity\Community $community
     * @return Subjects
     */
    public function addCommunity(\Network\StoreBundle\Entity\Community $community)
    {
        $this->community[] = $community;

        return $this;
    }

    /**
     * Remove community
     *
     * @param \Network\StoreBundle\Entity\Community $community
     * @return Subjects
     */
    public function removeCommunity(\Network\StoreBundle\Entity\Community $community)
    {
        $this->community->removeElement($community);
        
        return $this;
    }

    /**
     * Get community
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCommunity()
    {
        return $this->community;
    }
}
