<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Faculty
 *
 * @ORM\Table(name="faculty")
 * @ORM\Entity
 */
class Faculty
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="University", inversedBy="faculties")
     */
    private $university;

    /**
     * @ORM\OneToMany(targetEntity="Chair", mappedBy="faculty", cascade={"persist"})
     */
    private $chairs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->chairs = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
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
     * @param stdClass      $stdClass
     * @param EntityManager $manager
     *
     * @return $this
     */
    public function setFromStdClass($stdClass, $manager)
    {
        $this->id = $stdClass->id;
        $this->name = $stdClass->title;
        $this->university = $manager->find('NetworkStoreBundle:University', $stdClass->university_id);

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Faculty
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
     * Set university
     *
     * @param University $university
     *
     * @return Faculty
     */
    public function setUniversity(University $university = null)
    {
        if ($this->university != $university) {
            $this->university = $university;
            if ($university) {
                $university->addFaculty($this);
            }
        }

        return $this;
    }

    /**
     * Get university
     *
     * @return University
     */
    public function getUniversity()
    {
        return $this->university;
    }

    /**
     * @param string $parent
     *
     * @return Entity|null
     */
    private function _getParent($parent)
    {
        $getter = 'get' . $parent;

        return $this->university != null ? $this->university->$getter() : null;
    }
    /**
     * @return City|null
     */
    public function getCity()
    {
        return $this->_getParent('City');
    }

    /**
     * @return Country|null
     */
    public function getCountry()
    {
        return $this->_getParent('Country');
    }

    /**
     * Add chairs
     *
     * @param Chair $chair
     *
     * @return Faculty
     */
    public function addChair(Chair $chair)
    {
        if (!$this->chairs->contains($chair)) {
            $this->chairs[] = $chair;
            $chair->setFaculty($this);
        }

        return $this;
    }

    /**
     * Remove chairs
     *
     * @param Chair $chair
     */
    public function removeChair(Chair $chair)
    {
        $this->chairs->removeElement($chair);
        $chair->setFaculty();
    }

    /**
     * Get chairs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChairs()
    {
        return $this->chairs;
    }

    /**
     * @return string parent entity
     */
    public static function getParent()
    {
        return 'University';
    }
}
