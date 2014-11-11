<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * user
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
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
     * Set salt
     *
     * @param string $salt
     * @return user
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @param string $firstName
     * @return user
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
     * @return user
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
     * @return user
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
     * @return user
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
     * @return user
     */
    public function setEmail($email)
    {
        parent::setEmail($email);

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

    public function hash($encoder)
    {
        $salt = md5(openssl_random_pseudo_bytes(40));
        $password = $encoder->encodePassword($this->password, $salt);
        $this->setPassword($password)->setSalt($salt);
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
     * @return user
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
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
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
}
