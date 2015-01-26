<?php

namespace Network\StatisticBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="parsed_courses")
 * @ORM\Entity
 * ParsedCourse
 */
class ParsedCourse
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     *
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var float
     * @ORM\Column(name="timed", type="float")
     */
    private $timed;

    /**
     * @var float
     * @ORM\Column(name="normal", type="float")
     */
    private $normal;

    /**
     * @var integer
     * @ORM\Column(name="students", type="integer")
     */
    private $students;

    /**
     * @var integer
     * @ORM\Column(name="ok_students", type="integer")
     */
    private $okStudents;


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
     * @return ParsedCourse
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
     * Set timed
     *
     * @param float $timed
     * @return ParsedCourse
     */
    public function setTimed($timed)
    {
        $this->timed = $timed;

        return $this;
    }

    /**
     * Get timed
     *
     * @return float 
     */
    public function getTimed()
    {
        return $this->timed;
    }

    /**
     * Set normal
     *
     * @param float $normal
     * @return ParsedCourse
     */
    public function setNormal($normal)
    {
        $this->normal = $normal;

        return $this;
    }

    /**
     * Get normal
     *
     * @return float 
     */
    public function getNormal()
    {
        return $this->normal;
    }

    /**
     * Set students
     *
     * @param integer $students
     * @return ParsedCourse
     */
    public function setStudents($students)
    {
        $this->students = $students;

        return $this;
    }

    /**
     * Get students
     *
     * @return integer 
     */
    public function getStudents()
    {
        return $this->students;
    }

    /**
     * Set okStudents
     *
     * @param integer $okStudents
     * @return ParsedCourse
     */
    public function setOkStudents($okStudents)
    {
        $this->okStudents = $okStudents;

        return $this;
    }

    /**
     * Get okStudents
     *
     * @return integer 
     */
    public function getOkStudents()
    {
        return $this->okStudents;
    }

    public function __construct($id)
    {
        $this->id = $id;
    }
}
