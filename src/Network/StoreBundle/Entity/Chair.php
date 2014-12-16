<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chair
 *
 * @ORM\Table(name="chair")
 * @ORM\Entity
 */
class Chair
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
     * @ORM\ManyToOne(targetEntity="Faculty", inversedBy="chairs")
     */
    private $faculty;

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
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * @param stdClass      $stdClass
     * @param EntityManger  $manager
     *
     * @return $this
     */

    public function setFromStdClass($stdClass, $manager)
    {
        $this->id = $stdClass->id;
        $this->name = $stdClass->title;
        $this->faculty = $manager->find('NetworkStoreBundle:Faculty', $stdClass->faculty_id);

        return $this;
    }


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Chair
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
     * Set faculty
     *
     * @param Faculty $faculty
     *
     * @return Chair
     */
    public function setFaculty(Faculty $faculty = null)
    {
        if ($this->faculty != $faculty) {
            $this->faculty = $faculty;
            if ($faculty) {
                $faculty->addChair($this);
            }
        }

        return $this;
    }

    private function _getParent($parent)
    {
        $getter = 'get' . $parent;

        return $this->faculty != null ? $this->faculty->$getter() : null;
    }

    /**
     * Get faculty
     *
     * @return Faculty
     */
    public function getFaculty()
    {
        return $this->faculty;
    }

    /**
     * @return University|null
     */
    public function getUniversity()
    {
        return $this->_getParent('University');
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
     * @return string parent entity
     */
    public static function getParent()
    {
        return 'Faculty';
    }
}
