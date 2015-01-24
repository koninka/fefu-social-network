<?php

namespace Network\StoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Network\StoreBundle\DBAL\TypeCommunityEnumType;

/**
 * community
 *
 * @ORM\Table(name="community")
 * @ORM\Entity(repositoryClass="Network\StoreBundle\Repository\CommunityRepository")
 */
class Community
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
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var text
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="Subjects", inversedBy="community")
     * @ORM\JoinColumn(name="subjects_id", referencedColumnName="id")
     * 
     */
    private $subjects;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="typeCommunityEnumType")
     * @Assert\NotBlank()
     */
    private $type;
    
    /**
     * @var string
     *
     * @ORM\Column(name="view", type="viewCommunityEnumType")
     * @Assert\NotBlank()
     */
    private $view;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Thread", cascade="persist")
     * @ORM\JoinTable(name="communities_walls",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="thread_id", referencedColumnName="id", unique=true)}
     * )
     */
    private $wallThreads;

    public function __construct()
    {
        $this->type = TypeCommunityEnumType::C_OPEN;
        $this->wallThreads = new ArrayCollection();
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
     * @return Community
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

    /**
     * Set description
     *
     * @param text $description
     * @return Community
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set view
     *
     * @param string $view
     * @return \Network\StoreBundle\Entity\User
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get view
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }
    
    /**
     * Set type
     *
     * @param string $type
     * @return \Network\StoreBundle\Entity\User
     */
    public function setType($type)
    {
        $this->type = $type;

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
     * Set owner
     *
     * @param \Network\StoreBundle\Entity\User $owner
     * @return Community
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
    
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set subjects
     *
     * @param \Network\StoreBundle\Entity\Subjects $subjects
     * @return Community
     */
    public function setSubjects(\Network\StoreBundle\Entity\Subjects $subjects = null)
    {
        $this->subjects = $subjects;

        return $this;
    }

    /**
     * Get subjects
     *
     * @return \Network\StoreBundle\Entity\Subjects 
     */
    public function getSubjects()
    {
        return $this->subjects;
    }


    /**
     * @return ArrayCollection
     */
    public function getWallThreads()
    {
        return $this->wallThreads;
    }

    /**
     * @param ArrayCollection $wallThreads
     *
     * @return User
     */
    public function setWallThreads($wallThreads)
    {
        $this->wallThreads = $wallThreads;

        return $this;
    }

    /**
     * @param Thread $thread
     *
     * @return Community
     */
    public function addWallThread(Thread $thread)
    {
        $this->wallThreads->add($thread);

        return $this;
    }

    /**
     * @param Thread $thread
     *
     * @return Community
     */
    public function removeWallThread(Thread $thread)
    {
        $this->wallThreads->removeElement($thread);

        return $this;
    }
}
