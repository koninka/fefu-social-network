<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * UserGallery
 *
 * @ORM\Table(name="user_gallery")
 * @ORM\Entity(repositoryClass="Network\StoreBundle\Repository\UserGalleryRepository")
 */
class UserGallery
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
    protected $owner;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    protected $gallery;

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
     * Set owner
     *
     * @param \Network\StoreBundle\Entity\User $owner
     * @return \Network\StoreBundle\Entity\UserGallery
     */
    public function setOwner(\Network\StoreBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Network\StoreBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set gallery
     *
     * @param \Application\Sonata\MediaBundle\Entity\Gallery $gallery
     * @return \Network\StoreBundle\Entity\UserGallery
     */
    public function setGallery(\Application\Sonata\MediaBundle\Entity\Gallery $gallery = null)
    {
        $this->gallery = $gallery;

        return $this;
    }

    /**
     * Get gallery
     *
     * @return \Application\Sonata\MediaBundle\Entity\Gallery
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    public function __toString()
    {
        return null;
    }
}
