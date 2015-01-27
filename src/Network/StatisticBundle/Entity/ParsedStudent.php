<?php

namespace Network\StatisticBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Network\StoreBundle\Entity\User;

/**
 * @ORM\Table(name="parsed_students")
 * @ORM\Entity
 * ParsedStudent
 */
class ParsedStudent
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     *
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Network\StoreBundle\Entity\User")
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    private $email;

    /**
     * @var integer
     * @ORM\Column(name="webtest_id", type="integer")
     */
    private $webtestId;

    /**
     * @var string
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return int
     */
    public function getWebtestId()
    {
        return $this->webtestId;
    }

    /**
     * @param int $webtestId
     */
    public function setWebtestId($webtestId)
    {
        $this->webtestId = $webtestId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

}