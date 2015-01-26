<?php

namespace Network\StatisticBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="parsed_tasks")
 * @ORM\Entity
 * ParsedTask
 */
class ParsedTask
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     *
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="normal_weight", type="float")
     */
    private $normalWeight;

    /**
     * @var integer
     *
     * @ORM\Column(name="multi_user", type="integer", nullable=true)
     */
    private $multiUser;

    /**
     * @var integer
     *
     * @ORM\Column(name="weight", type="integer")
     */
    private $weight;

    /**
     * @var integer
     *
     * @ORM\Column(name="after_marks_fix", type="integer")
     */
    private $afterMarksFix;

    /**
     * @var integer
     *
     * @ORM\Column(name="half_life", type="integer")
     */
    private $halfLife;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="due_date", type="datetime")
     */
    private $dueDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="past_due_date", type="integer")
     */
    private $pastDueDate;

    /**
     * @var float
     *
     * @ORM\Column(name="avg", type="float", nullable=true)
     */
    private $avg;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="user_name", type="string")
     */
    private $userName;

    /**
     * @var float
     *
     * @ORM\Column(name="max_points", type="float")
     */
    private $maxPoints;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="course_id", type="integer")
     */
    private $courseId;

    /**
     * @var float
     *
     * @ORM\Column(name="deprecation_limit", type="float")
     */
    private $deprecationLimit;


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
     * Set normalWeight
     *
     * @param float $normalWeight
     * @return ParsedTask
     */
    public function setNormalWeight($normalWeight)
    {
        $this->normalWeight = $normalWeight;

        return $this;
    }

    /**
     * Get normalWeight
     *
     * @return float 
     */
    public function getNormalWeight()
    {
        return $this->normalWeight;
    }

    /**
     * Set multiUser
     *
     * @param string $multiUser
     * @return ParsedTask
     */
    public function setMultiUser($multiUser)
    {
        $this->multiUser = $multiUser;

        return $this;
    }

    /**
     * Get multiUser
     *
     * @return string 
     */
    public function getMultiUser()
    {
        return $this->multiUser;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     * @return ParsedTask
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return integer 
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set afterMarksFix
     *
     * @param integer $afterMarksFix
     * @return ParsedTask
     */
    public function setAfterMarksFix($afterMarksFix)
    {
        $this->afterMarksFix = $afterMarksFix;

        return $this;
    }

    /**
     * Get afterMarksFix
     *
     * @return integer
     */
    public function getAfterMarksFix()
    {
        return $this->afterMarksFix;
    }

    /**
     * Set halfLife
     *
     * @param integer $halfLife
     * @return ParsedTask
     */
    public function setHalfLife($halfLife)
    {
        $this->halfLife = $halfLife;

        return $this;
    }

    /**
     * Get halfLife
     *
     * @return integer 
     */
    public function getHalfLife()
    {
        return $this->halfLife;
    }

    /**
     * Set pastDueDate
     *
     * @param integer $pastDueDate
     * @return ParsedTask
     */
    public function setPastDueDate($pastDueDate)
    {
        $this->pastDueDate = $pastDueDate;

        return $this;
    }

    /**
     * Get pastDueDate
     *
     * @return integer 
     */
    public function getPastDueDate()
    {
        return $this->pastDueDate;
    }

    /**
     * Set avg
     *
     * @param float $avg
     * @return ParsedTask
     */
    public function setAvg($avg)
    {
        $this->avg = $avg;

        return $this;
    }

    /**
     * Get avg
     *
     * @return float 
     */
    public function getAvg()
    {
        return $this->avg;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ParsedTask
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
     * Set userName
     *
     * @param string $userName
     * @return ParsedTask
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string 
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set maxPoints
     *
     * @param float $maxPoints
     * @return ParsedTask
     */
    public function setMaxPoints($maxPoints)
    {
        $this->maxPoints = $maxPoints;

        return $this;
    }

    /**
     * Get maxPoints
     *
     * @return float 
     */
    public function getMaxPoints()
    {
        return $this->maxPoints;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return ParsedTask
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set courseId
     *
     * @param integer $courseId
     * @return ParsedTask
     */
    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;

        return $this;
    }

    /**
     * Get courseId
     *
     * @return integer 
     */
    public function getCourseId()
    {
        return $this->courseId;
    }

    /**
     * Set deprecationLimit
     *
     * @param float $deprecationLimit
     * @return ParsedTask
     */
    public function setDeprecationLimit($deprecationLimit)
    {
        $this->deprecationLimit = $deprecationLimit;

        return $this;
    }

    /**
     * Get deprecationLimit
     *
     * @return float 
     */
    public function getDeprecationLimit()
    {
        return $this->deprecationLimit;
    }

    /**
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param \DateTime $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    public function __construct($id)
    {
        $this->id = $id;
    }
}
