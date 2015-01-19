<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Network\StoreBundle\DBAL\TypeCommunityEnumType;

/**
 * Community
 *
 * @ORM\Table(name="community")
 * @ORM\Entity
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

    public function __construct()
    {
        $this->type = TypeCommunityEnumType::C_OPEN;
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
}
