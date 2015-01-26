<?php

namespace Network\StatisticBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="parsed_students_ratings")
 * @ORM\Entity
 * ParsedStudentRating
 */
class ParsedStudentRating
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(name="student_id", type="integer")
     */
    private $studentId;

    /**
     * @var integer
     * @ORM\Column(name="course_id", type="integer")
     */
    private $courseId;

    /**
     * @var integer
     * @ORM\Column(name="ok", type="integer")
     */
    private $ok;

    /**
     * @var float
     * @ORM\Column(name="timed", type="float")
     */
    private $timed;

    /**
     * @var integer
     * @ORM\Column(name="tasks", type="integer")
     */
    private $tasks;

    /**
     * @var float
     * @ORM\Column(name="normal", type="float")
     */
    private $normal;

    /**
     * @var string
     * @ORM\Column(name="result", type="string")
     */
    private $result;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * @return int
     */
    public function getCourseId()
    {
        return $this->courseId;
    }

    /**
     * @param int $courseId
     */
    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;
    }

    /**
     * @return int
     */
    public function getOk()
    {
        return $this->ok;
    }

    /**
     * @param int $ok
     */
    public function setOk($ok)
    {
        $this->ok = $ok;
    }

    /**
     * @return float
     */
    public function getTimed()
    {
        return $this->timed;
    }

    /**
     * @param float $timed
     */
    public function setTimed($timed)
    {
        $this->timed = $timed;
    }

    /**
     * @return int
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param int $tasks
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * @return float
     */
    public function getNormal()
    {
        return $this->normal;
    }

    /**
     * @param float $normal
     */
    public function setNormal($normal)
    {
        $this->normal = $normal;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

}