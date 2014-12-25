<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ContactInfo
 *
 * @ORM\Table(name="contact_info")
 * @ORM\Entity
 * @UniqueEntity("additionalEmail", message="This email already exists")
 * @UniqueEntity("skype", message="This skype already exists")
 */

class ContactInfo
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
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="User", mappedBy="contactInfo")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="skype", type="string", length=255, nullable=true, unique=true)
     */
    private $skype;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_email", type="string", length=255, nullable=true, unique=true)
     * @Assert\Email
     *
     */
    private $additionalEmail;

    /**
     * @ORM\ManyToMany(targetEntity="Address", cascade={"persist"})
     * @ORM\JoinTable(name="users_address",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="address_id", referencedColumnName="id")}
     *      )
     **/
    private $address;

    /**
     * @ORM\OneToMany(targetEntity="Phonenumber", mappedBy="contactInfo", cascade={"persist"}, orphanRemoval=true)
     **/
    private $phone;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->address = new \Doctrine\Common\Collections\ArrayCollection();
        $this->phone = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set skype
     *
     * @param string $skype
     * @return ContactInfo
     */
    public function setSkype($skype)
    {
        $this->skype = $skype;

        return $this;
    }

    /**
     * Get skype
     *
     * @return string
     */
    public function getSkype()
    {
        return $this->skype;
    }

    /**
     * Set additionalEmail
     *
     * @param string $additionalEmail
     * @return ContactInfo
     */
    public function setAdditionalEmail($additionalEmail)
    {
        $this->additionalEmail = $additionalEmail;

        return $this;
    }

    /**
     * Get additionalEmail
     *
     * @return string
     */
    public function getAdditionalEmail()
    {
        return $this->additionalEmail;
    }

    /**
     * Set user
     *
     * @param \Network\StoreBundle\Entity\User $user
     * @return ContactInfo
     */
    public function setUser(\Network\StoreBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Network\StoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function __toString()
    {
        $address  = '';
        foreach ($this->address->getValues() as $val) {
            $address .= sprintf('Address: %s; ', $val);
        }

        $phonenumbers = '';
        foreach ($this->phone->getValues() as $val) {
            $phonenumbers .= sprintf('Phone number: %s; ', $val);
        }

        $skype = !empty($this->skype) ? sprintf('skype: %s;', $this->skype) : '';

        $additionalEmail = !empty($this->additionalEmail)
            ? sprintf('additionalEmail: %s', $this->additionalEmail)
            : '';
        return sprintf('%s %s %s %s', $skype, $address, $phonenumbers, $additionalEmail);
    }

    /**
     * Add phone
     *
     * @param \Network\StoreBundle\Entity\Phonenumber $phone
     * @return ContactInfo
     */
    public function addPhone(\Network\StoreBundle\Entity\Phonenumber $phone)
    {
        $this->phone[] = $phone;
        $phone->setContactInfo($this);

        return $this;
    }

    /**
     * Remove phone
     *
     * @param \Network\StoreBundle\Entity\Phonenumber $phone
     * @return ContactInfo
     */
    public function removePhone(\Network\StoreBundle\Entity\Phonenumber $phone)
    {
        $this->phone->removeElement($phone);
        $phone->setContactInfo(null);

        return $this;
    }

    /**
     * Get phone
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhone()
    {
        foreach ($this->phone as $phone) {
            $phone->setContactInfo($this);
        }

        return $this->phone;
    }
    /**
     * Set phone
     *
     * @param \Doctrine\Common\Collections\Collection  $phones
     * @return ContactInfo
     */
    public function setPhone(\Doctrine\Common\Collections\Collection  $phones)
    {
        $this->phone = $phones;
        foreach ($phones as $phone) {
            $phone->setContactInfo($this);
        }

        return $this;
    }

    /**
     * Add address
     *
     * @param \Network\StoreBundle\Entity\Address $address
     * @return ContactInfo
     */
    public function addAddress(\Network\StoreBundle\Entity\Address $address)
    {
        $this->address[] = $address;

        return $this;
    }

    /**
     * Remove address
     *
     * @param \Network\StoreBundle\Entity\Address $address
     * @return ContactInfo
     */
    public function removeAddress(\Network\StoreBundle\Entity\Address $address)
    {
        $this->address->removeElement($address);

        return $this;
    }

    /**
     * Get address
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address
     *
     * @param \Doctrine\Common\Collections\Collection  $address
     * @return ContactInfo
     */
    public function setAddress(\Doctrine\Common\Collections\Collection  $address)
    {
        $this->address = $address;

        return $this;
    }
}
