<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * University
 *
 * @ORM\Table(name="university")
 * @ORM\Entity
 */
class University
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
     * @ORM\ManyToOne(targetEntity="City", inversedBy="universities")
     */
    private $city;

    /**
     * @ORM\OneToMany(targetEntity="Faculty", mappedBy="university", cascade={"persist"})
     */
    private $faculties;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->faculties = new ArrayCollection();
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
        $this->city = $manager->find('NetworkStoreBundle:City', $stdClass->city_id);

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return University
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
     * Set city
     *
     * @param City $city
     *
     * @return University
     */
    public function setCity(City $city = null)
    {
        if ($this->city != $city) {
            $this->city = $city;
            if ($city) {
                $city->addUniversity($this);
            }
        }

        return $this;
    }

    /**
     * Get city
     *
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return Country|null
     */
    public function getCountry()
    {
        $city = $this->getCity();

        return  ($city != null) ? $city->getCountry() : null;
    }
    /**
     * Get faculties
     *
     * @return Collection
     */
    public function getFaculties()
    {
        return $this->faculties;
    }

    /**
     * @param Faculty $faculty
     *
     * @return University
     */
    public function addFaculty(Faculty $faculty)
    {
        if (!$this->faculties->contains($faculty)) {
            $this->faculties[] = $faculty;
            $faculty->setUniversity($this);
        }

        return $this;
    }

    /**
     * Remove faculty
     *
     * @param Faculty $faculty
     */
    public function removeFaculty($faculty)
    {
        $this->faculties->removeElement($faculty);
        $faculty->setUniversity();
    }
    /**
     * @return string parent entity
     */
    public static function getParent()
    {
        return 'City';
    }
}
