<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * School
 *
 * @ORM\Table(name="school")
 * @ORM\Entity
 */
class School
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
     * @ORM\ManyToOne(targetEntity="City", inversedBy="schools")
     */
    private $city;

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
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return School
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
     * @return School
     */
    public function setCity(City $city = null)
    {
        if ($this->city != $city) {
            $this->city = $city;
            if ($city) {
                $city->addSchool($this);
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
     * @return string parent entity
     */
    public static function getParent()
    {
        return 'City';
    }
}
