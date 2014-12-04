<?php
namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="job")
 * @ORM\Entity
 */
class Job
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * NotShowInForm!
     */
    private $id;

    /**
     * @var \Network\StoreBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="jobs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="employer", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $employer;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $city;

    /**
     * @var date
     *
     * @ORM\Column(name="start_date", type="date")
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $startDate;

    /**
     * @var date
     *
     * @ORM\Column(name="finish_date", type="date", nullable=true)
     * @Assert\Date()
     */
    private $finishDate;


    /**
     * @var JobPost
     *
     * @ORM\ManyToOne(targetEntity="JobPost")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id")
     */
    private $post;

    /**
     * @param \Network\StoreBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \Network\StoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $city
     * @return \Network\StoreBundle\Entity\Job
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $country
     * @return \Network\StoreBundle\Entity\Job
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param int $id
     * @return \Network\StoreBundle\Entity\Job
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Network\StoreBundle\Entity\date $finishDate
     * @return \Network\StoreBundle\Entity\Job
     */
    public function setFinishDate($finishDate)
    {
        $this->finishDate = $finishDate;

        return $this;
    }

    /**
     * @return \Network\StoreBundle\Entity\date
     */
    public function getFinishDate()
    {
        return $this->finishDate;
    }

    /**
     * @param \Network\StoreBundle\Entity\date $startDate
     * @return \Network\StoreBundle\Entity\Job
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \Network\StoreBundle\Entity\date
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param string $employer
     * @return \Network\StoreBundle\Entity\Job
     */
    public function setEmployer($employer)
    {
        $this->employer= $employer;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmployer()
    {
        return $this->employer;
    }

    /**
     * @param JobPost $post
     * @return \Network\StoreBundle\Entity\Job
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return JobPost
     */
    public function getPost()
    {
        return $this->post;
    }
}
