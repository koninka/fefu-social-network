<?php
namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="job_posts")
 * @ORM\Entity
 */
class JobPost
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="predefined", type="boolean")
     */
    private $predefined;

    /**
     * @param int $id
     *
     * @return Network\StoreBundle\Entity\JobPost
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
     * @param string $name
     *
     * @return Network\StoreBundle\Entity\JobPost
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param boolean $predefined
     *
     * @return Network\StoreBundle\Entity\JobPost
     */
    public function setPredefined($predefined)
    {
        $this->predefined = $predefined;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPredefined()
    {
        return $this->predefined;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
