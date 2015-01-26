<?php

namespace Network\StatisticBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="parsed_crits")
 * @ORM\Entity
 * ParsedCrit
 */
class ParsedCrit
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
     * @ORM\Column(name="min_timed_rating", type="float", nullable=true)
     */
    private $minTimedRating;

    /**
     * @var float
     * @ORM\Column(name="min_rating", type="float")
     */
    private $minRating;

    /**
     * @var integer
     * @ORM\Column(name="min_tasks", type="integer", nullable=true)
     */
    private $minTasks;

    /**
     * @var string
     * @ORM\Column(name="academic_year", type="string")
     */
    private $academicYear;

    /**
     * @var integer
     * @ORM\Column(name="passable", type="integer")
     */
    private $passable;

    /**
     * @var integer
     * @ORM\Column(name="rank", type="integer")
     */
    private $rank;

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
     * @return float
     */
    public function getMinTimedRating()
    {
        return $this->minTimedRating;
    }

    /**
     * @param float $minTimedRating
     */
    public function setMinTimedRating($minTimedRating)
    {
        $this->minTimedRating = $minTimedRating;
    }

    /**
     * @return float
     */
    public function getMinRating()
    {
        return $this->minRating;
    }

    /**
     * @param float $minRating
     */
    public function setMinRating($minRating)
    {
        $this->minRating = $minRating;
    }

    /**
     * @return int
     */
    public function getMinTasks()
    {
        return $this->minTasks;
    }

    /**
     * @param int $minTasks
     */
    public function setMinTasks($minTasks)
    {
        $this->minTasks = $minTasks;
    }

    /**
     * @return string
     */
    public function getAcademicYear()
    {
        return $this->academicYear;
    }

    /**
     * @param string $academicYear
     */
    public function setAcademicYear($academicYear)
    {
        $this->academicYear = $academicYear;
    }

    /**
     * @return int
     */
    public function getPassable()
    {
        return $this->passable;
    }

    /**
     * @param int $passable
     */
    public function setPassable($passable)
    {
        $this->passable = $passable;
    }

    /**
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param int $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
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

    public function __construct($id)
    {
        $this->id = $id;
    }

}