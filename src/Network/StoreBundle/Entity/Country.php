<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * County
 *
 * @ORM\Table(name="country")
 * @ORM\Entity
 */
class Country
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
     * @ORM\OneToMany(targetEntity="City", mappedBy="country", cascade={"persist"})
     */
    private $cities;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cities = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
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

        return $this;
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
     * Set name
     *
     * @param string $name
     *
     * @return Country
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
     * Add city
     *
     * @param City $city
     *
     * @return Country
     */
    public function addCity($city)
    {
        if (!$this->cities->contains($city)) {
            $this->cities[] = $city;
            $city->setCountry($this);
        }

        return $this;
    }

    /**
     * Remove city
     *
     * @param City $city
     */
    public function removeCity($city)
    {
        $this->cities->removeElement($city);
        $city->setCountry();
    }

    /**
     * Get cities
     *
     * @return Collection
     */
    public function getCities()
    {
        return $this->cities;
    }
}
