<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * user
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 * @UniqueEntity("email")
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * NotShowInForm!
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\Email()
     * @Assert\NotBlank()
     */
    protected $email;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Network\StoreBundle\Entity\Group")
     * @ORM\JoinTable(name="user_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    /**
     * @ORM\OneToMany(targetEntity="Relationship", mappedBy="user", cascade={"persist"})
     */
    protected $relationships;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * @var string
     *
     * @Assert\Length(min=6, max=150)
     */
    protected $password;

    /**
     * @var string
     *
     * NotShowInForm!
     */
    protected $salt;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=200)
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=200)
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="genderEnumType")
     * @Assert\NotBlank()
     */
    private $gender;

    /**
     * @var date
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     * @Assert\Date()
     */
    private $birthday;


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Job", mappedBy="user", cascade="persist")
     */
    private $jobs;

    /**
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="ContactInfo", inversedBy = "user", cascade = {"persist"})
     * @ORM\JoinColumn(name="contact_info_id", referencedColumnName="id")
     */
    private $contactInfo;

    /**
     * Set salt
     *
     * @param string $salt
     * @return \Network\StoreBundle\Entity\User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @param string $firstName
     * @return \Network\StoreBundle\Entity\User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $gender
     * @return \Network\StoreBundle\Entity\User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $lastName
     * @return \Network\StoreBundle\Entity\User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param \Network\StoreBundle\Entity\date $birthday
     * @return \Network\StoreBundle\Entity\User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return \Network\StoreBundle\Entity\date
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param string $email
     * @return \Network\StoreBundle\Entity\User
     */
    public function setEmail($email)
    {
        parent::setEmail($email);
        if (empty($this->username)) {
            $this->setUsername($email);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @return \DateTime
     */
    public function getCredentialsExpireAt()
    {
        return $this->credentialsExpireAt;
    }

    /**
     * @param mixed $jobs
     */
    public function setJobs($jobs)
    {
        $this->jobs = $jobs;
    }

    /**
     * @return mixed
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    public function hash($encoder)
    {
        $salt = md5(openssl_random_pseudo_bytes(40));
        $password = $encoder->encodePassword($this->password, $salt);
        $this->setPassword($password)->setSalt($salt);

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param mixed $groups
     * @return \Network\StoreBundle\Entity\User
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->jobs = new ArrayCollection();
        $this->relationships = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->posts = new ArrayCollection();
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
     * Set contactInfo
     *
     * @param \Network\StoreBundle\Entity\ContactInfo $contactInfo
     * @return \Network\StoreBundle\Entity\User
     */

    public function setContactInfo(\Network\StoreBundle\Entity\ContactInfo $contactInfo = null)
    {
        $this->contactInfo = $contactInfo;

        return $this;
    }

    /**
     * Get contactInfo
     *
     * @return \Network\StoreBundle\Entity\ContactInfo
     */
    public function getContactInfo()
    {
        return $this->contactInfo;
    }

    /**
     * Add relationships
     *
     * @param \Network\StoreBundle\Entity\Relationship $partner
     * @return \Network\StoreBundle\Entity\User
     */
    public function addRelationship(\Network\StoreBundle\Entity\Relationship $partner)
    {
        if (!$this->getRelationships()->contains($partner)) {
            $this->relationships[] = $partner;
        }

        return $this;
    }

    /**
     * Remove relationships
     *
     * @param \Network\StoreBundle\Entity\Relationship $partner
     * @return \Network\StoreBundle\Entity\User
     */
    public function removeRelationship(\Network\StoreBundle\Entity\Relationship $partner)
    {
        if (!$this->getRelationships()->contains($partner)) {
            $this->relationships->removeElement($partner);
        }

        return $this;
    }

    /**
     * Get relationships
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    public function getRelationshipByIds()
    {
        $ids = [];
        foreach ($this->getRelationships() as $relationship) {
            $ids[$relationship->getPartner()->getId()] = $relationship;
        }

        return $ids;
    }

    /**
     *
     * @param integer $partner
     * @param string $status
     * @return boolean
     */
    public function hasRelationship($partner, $status)
    {
        $rels = $this->getRelationshipByIds();
        if (array_key_exists($partner, $rels)) {
            return $rels[$partner]->getStatus() === $status;
        }
        return false;

    }

    /**
     * @param integer $partner
     * @return Relationship
     */
    public function getRelationship($partner)
    {
        $rels = $this->getRelationshipByIds();
        if (array_key_exists($partner, $rels)) {
            return $rels[$partner];
        }
        return NULL;
    }

    /**
     * @param integer $partner
     * @return string
     */
    public function getRelationshipStatus($partner)
    {
        $rel = $this->getRelationship($partner);
        if ($rel) {
            return $rel->getStatus();
        }

        return RelationshipStatusEnumType::FS_NONE;
    }

    public function __toString()
    {
        return $this->username;
    }

    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="user")
     */
    protected $posts;

    /**
     * Add posts
     *
     * @param \Network\StoreBundle\Entity\Post $posts
     * @return User
     */
    public function addPost(\Network\StoreBundle\Entity\Post $posts)
    {
        $this->posts[] = $posts;

        return $this;
    }

    /**
     * Remove posts
     *
     * @param \Network\StoreBundle\Entity\Post $posts
     */
    public function removePost(\Network\StoreBundle\Entity\Post $posts)
    {
        $this->posts->removeElement($posts);
    }

    /**
     * Get posts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPosts()
    {
        return $this->posts;
    }
}
