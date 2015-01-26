<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Yaml\Tests\A;
use Application\Sonata\MediaBundle\Entity\Media as MediaInterface;

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
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="UserCommunity", mappedBy="user", cascade={"persist"})
     */
    protected $communities;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Poll", mappedBy="user", cascade={"persist"})
     */
    protected $poll;

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
     * @var string
     *
     * @ORM\Column(name="vk_login", type="string", nullable=true)
     */
    private $vkLogin;

    /**
     * @var string
     *
     * @ORM\Column(name="github_login", type="string", nullable=true)
     */
    private $githubLogin;

     /**
     * @var string
     *
     * @ORM\Column(name="google_login", type="string", nullable=true)
     */
    private $googleLogin;

    /**
     * @var string
     *
     * @ORM\Column(name="fb_login", type="string", nullable=true)
     */
    private $fbLogin;

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
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="MP3Record", inversedBy="users")
     * @ORM\JoinTable(name="users_mp3s")
     */
    private $mp3s;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Thread", cascade="persist", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="users_walls",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="thread_id", referencedColumnName="id", unique=true)}
     * )
     */
    private $wallThreads;

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
     * @param string $vkLogin
     * @return \Network\StoreBundle\Entity\User
     */
    public function setVkLogin($vkLogin)
    {
        $this->vkLogin = $vkLogin;

        return $this;
    }

    /**
     * @return string
     */
    public function getVkLogin()
    {
        return $this->vkLogin;
    }

    /**
     * @return string
     */
    public function getGithubLogin()
    {
        return $this->githubLogin;
    }

    /**
     * @param string $githubLogin
     * @return \Network\StoreBundle\Entity\User
     */
    public function setGithubLogin($githubLogin)
    {
        $this->githubLogin = $githubLogin;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleLogin()
    {
        return $this->googleLogin;
    }

    /**
     * @param string $googleLogin
     * @return \Network\StoreBundle\Entity\User
     */
    public function setGoogleLogin($googleLogin)
    {
        $this->googleLogin = $googleLogin;

        return $this;
    }

    /**
     * @return string
     */
    public function getFbLogin()
    {
        return $this->fbLogin;
    }

    /**
     * @param string $fbLogin
     * @return \Network\StoreBundle\Entity\User
     */
    public function setFbLogin($fbLogin)
    {
        $this->fbLogin = $fbLogin;

        return $this;
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
        $this->communities = new ArrayCollection();
        $this->albums = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->threads = new ArrayCollection();
        $this->mp3s = new ArrayCollection();
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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->username;
    }

    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="user")
     */
    protected $posts;

    /**
     * @ORM\OneToMany(targetEntity="UserThread", mappedBy="user", cascade={"persist"})
     **/
    protected $userThreads;

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

    /**
     * Create UserThread Entity
     *
     * @param \Network\StoreBundle\Entity\Thread $thread
     * @return User
     */
    public function addThread(\Network\StoreBundle\Entity\Thread $thread)
    {
        $userThread = new UserThread($this, $thread, $this->getId());

        return $this;
    }

    /**
     * Add communities
     *
     * @param \Network\StoreBundle\Entity\UserCommunity $communities
     * @return User
     */
    public function addCommunity(\Network\StoreBundle\Entity\UserCommunity $communities)
    {
        $this->communities[] = $communities;

        return $this;
    }

    /**
     * Remove threads
     *
     * @param \Network\StoreBundle\Entity\Thread $thread
     */
    public function removeThread(\Network\StoreBundle\Entity\Thread $thread)
    {
        foreach($this->userThreads as $userThread) {
            if ($userThread->getThread() == $thread) {
                $userThread->erase();
                break;
            }
        }
    }

    /**
     * Get threads
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getThreads()
    {
        $threads = new ArrayCollection();
        foreach($this->userThreads as $userThread) {
            $threads[] = $userThread->getThread();
        }

        return $threads;
    }

    /**
     * @param UserThread $userThread
     *
     * @return User
     */
    public function addUserThread($userThread)
    {
        $this->userThreads[] = $userThread;

        return $this;
    }
    
    /*
     * Remove communities
     *
     * @param \Network\StoreBundle\Entity\UserCommunity $communities
     */
    public function removeCommunity(\Network\StoreBundle\Entity\UserCommunity $communities)
    {
        $this->communities->removeElement($communities);
    }

    /**
     * Get communities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCommunities()
    {
        return $this->communities;
    }

    /**
     * @return ArrayCollection
     */
    public function getMp3s()
    {
        return $this->mp3s;
    }

    /**
     * @param ArrayCollection $mp3s
     *
     * @return User
     */
    public function setMp3s($mp3s)
    {
        $this->mp3s = $mp3s;

        return $this;
    }

     /**
     * @param MP3Record $mp3
     * @return User
     */
    public function addMp3(MP3Record $mp3)
    {
        $this->mp3s->add($mp3);

        return $this;
    }

    /**
     * @param MP3Record $mp3
     * @return User
     */
    public function removeMp3(MP3Record $mp3)
    {
        $this->mp3s->removeElement($mp3);

        return $this;
    }
     /**   
     * Add poll
     *
     * @param \Network\StoreBundle\Entity\Poll $poll
     * @return User
     */
    public function addPoll(\Network\StoreBundle\Entity\Poll $poll)
    {
        $this->poll[] = $poll;

        return $this;
    }
    /**
     * Remove poll
     *
     * @param \Network\StoreBundle\Entity\Poll $poll
     */
    public function removePoll(\Network\StoreBundle\Entity\Poll $poll)
    {
        $this->poll->removeElement($poll);

        return $this;
    }

    /**
     * @param MP3Record $mp3
     * @return bool
     */
    public function hasMp3InPlaylist(MP3Record $mp3)
    {
        return $this->mp3s->contains($mp3);
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
     * @return User
     */
    public function addWallThread(Thread $thread)
    {
        $this->wallThreads->add($thread);

        return $this;
    }

    /**
     * @param Thread $thread
     *
     * @return User
     */
    public function removeWallThread(Thread $thread)
    {
        $this->wallThreads->removeElement($thread);

        return $this;
    }

    /**
     * Get object type for wall-routes.
     *
     * @return string
     */
    public function getTypeForJsonRoute()
    {
        return 'user';
    }

     /**
     * @var \Application\Sonata\MediaBundle\Entity\Media
     * @ORM\OneToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"})
     */
    protected $avatar;

    /**
     * Set avatar
     *
     * @param MediaInterface $avatar
     * @return User
     */
    public function setAvatar(MediaInterface $avatar = null)
    {
        if ($avatar) {
            $this->avatar = $avatar;
        }

        return $this;
    }

    /**
     * Get avatar
     *
     * @return MediaInterface
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Remove avatar
     *
     * @param MediaInterface $avatar
     */
    public function removeAvatar(MediaInterface $avatar)
    {
        $this->avatar->removeElement($avatar);
    }

    /**
     * @ORM\OneToMany(targetEntity="UserGallery", mappedBy="owner", cascade={"persist"})
     */
    protected $albums;

    /**
     * Add albums
     *
     * @param \Network\StoreBundle\Entity\UserGallery $album
     * @return User
     */
    public function addAlbum(\Network\StoreBundle\Entity\UserGallery $album)
    {
        if (!$this->getAlbums()->contains($album)) {
            $this->albums[] = $album;
        }

        return $this;
    }

    /**
     * Remove albums
     *
     * @param \Network\StoreBundle\Entity\UserGallery $album
     */
    public function removeAlbum(\Network\StoreBundle\Entity\UserGallery $album)
    {
        $this->albums->removeElement($album);
    }

    /**
     * Get albums
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAlbums()
    {
        return $this->albums;
    }
        
    /**
     * Get poll
     *
     * @return \Doctrine\Common\Collections\Poll
     */
    public function getPoll()
    {
        return $this->poll;
    }
}
