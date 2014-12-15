<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * City
 *
 * @ORM\Table(name="city")
 * @ORM\Entity
 */
class City
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
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="cities")
     */
    private $country;

    /**
     * @ORM\OneToMany(targetEntity="University", mappedBy="city", cascade={"persist"})
     */
    private $universities;

    /**
     * @ORM\OneToMany(targetEntity="School", mappedBy="city", cascade={"persist"})
     */
    private $schools;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->schools = new ArrayCollection();
        $this->universities = new ArrayCollection();
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
        $this->country = $manager->find('NetworkStoreBundle:Country', $stdClass->country_id);

        return $this;
    }
    /**
     * Set name
     *
     * @param string $name
     *
     * @return City
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
     * Set country
     *
     * @param Country $country
     *
     * @return City
     */
    public function setCountry(Country $country = null)
    {
        if ($this->country != $country) {
            $this->country = $country;
            if ($country) {
                $country->addCity($this);
            }
        }

        return $this;
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Add universites
     *
     * @param University $university
     *
     * @return City
     */
    public function addUniversity(University $university)
    {
        if (!$this->universities->contains($university)) {
            $this->universities[] = $university;
            $university->setCity($this);
        }

        return $this;
    }

    /**
     * Remove universities
     *
     * @param University $universities
     */
    public function removeUniversity(University $universities)
    {
        $this->universities->removeElement($universities);
        $universities->setCity();
    }

    /**
     * Get universities
     *
     * @return Collection
     */
    public function getUniversities()
    {
        return $this->universities;
    }

    /**
     * Add schools
     *
     * @param School $school
     *
     * @return City
     */
    public function addSchool(School $school)
    {
        if (!$this->schools->contains($school)) {
            $this->schools[] = $school;
            $school->setCity($this);
        }

        return $this;
    }

    /**
     * Remove schools
     *
     * @param \Network\StoreBundle\Entity\School $schools
     */
    public function removeSchool(School $schools)
    {
        $this->schools->removeElement($schools);
        $schools->setCity();
    }

    /**
     * Get schools
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSchools()
    {
        return $this->schools;
    }

    /**
     * @return string parent entity
     */
    public static function getParent()
    {
        return 'Country';
    }
}
