<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Phonenumber
 *
 * @ORM\Table(name="contact_info_phonenumber")
 * @ORM\Entity
 */
class Phonenumber
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
     * @ORM\Column(name="phone", type="string", length=50)
     * @Assert\Regex( pattern="/^((8|0|\+\d{1,2})[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7}$/")
     */
    private $phonenumber;

    /**
     * @ORM\ManyToOne(targetEntity="ContactInfo", inversedBy="phone", cascade = {"persist"})
     * @ORM\JoinColumn(name="contact_info_id", referencedColumnName="id")
     **/
    private $contactInfo;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    public function __toString()
    {
        return $this->phonenumber;
    }

    /**
     * Set phonenumber
     *
     * @param string $phonenumber
     * @return Phonenumber
     */
    public function setPhonenumber($phonenumber)
    {
        $this->phonenumber = $phonenumber;

        return $this;
    }

    /**
     * Get phonenumber
     *
     * @return string
     */
    public function getPhonenumber()
    {
        return $this->phonenumber;
    }

    /**
     * Set contactInfo
     *
     * @param \Network\StoreBundle\Entity\ContactInfo $contactInfo
     * @return Phonenumber
     */
    public function setContactInfo(\Network\StoreBundle\Entity\ContactInfo $contactInfo = null)
    {
        $this->contactInfo = $contactInfo;

        return $this;
    }

    /**
     * Get contactInfo
     *
     * @return \Network\StoreBundle\Entity\ContactInfo
     */
    public function getContactInfo()
    {
        return $this->contactInfo;
    }
}
