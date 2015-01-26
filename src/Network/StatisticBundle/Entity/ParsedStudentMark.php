<?php

namespace Network\StatisticBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="parsed_students_marks")
 * @ORM\Entity
 * ParsedStudentMark
 */
class ParsedStudentMark
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
     * @ORM\Column(name="dep_points", type="float")
     */
    private $depPoints;

    /**
     * @var float
     * @ORM\Column(name="points", type="float")
     */
    private $points;

    /**
     * @var integer
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var integer
     * @ORM\Column(name="task_id", type="integer")
     */
    private $taskId;

    /**
     * @var \DateTime
     * @ORM\Column(name="submit_date", type="datetime")
     */
    private $submitDate;

    /**
     * @var integer
     * @ORM\Column(name="days_late", type="integer")
     */
    private $daysLate;

    /**
     * @var integer
     * @ORM\Column(name="student_id", type="integer")
     */
    private $studentId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getDepPoints()
    {
        return $this->depPoints;
    }

    /**
     * @param float $depPoints
     */
    public function setDepPoints($depPoints)
    {
        $this->depPoints = $depPoints;
    }

    /**
     * @return float
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param float $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * @param int $taskId
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;
    }

    /**
     * @return \DateTime
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * @param \DateTime $submitDate
     */
    public function setSubmitDate($submitDate)
    {
        $this->submitDate = $submitDate;
    }

    /**
     * @return int
     */
    public function getDaysLate()
    {
        return $this->daysLate;
    }

    /**
     * @param int $daysLate
     */
    public function setDaysLate($daysLate)
    {
        $this->daysLate = $daysLate;
    }

    /**
     * @return int
     */
    public function getStudentId()
    {
        return $this->studentId;
    }

    /**
     * @param int $studentId
     */
    public function setStudentId($studentId)
    {
        $this->studentId = $studentId;
    }

    public function __construct($id)
    {
        $this->id = $id;
    }

}