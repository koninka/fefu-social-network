<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fp\OpenIdBundle\Model\IdentityInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * OpenIdIdenity
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class OpenIdIdentity implements IdentityInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected  $id;

    /**
     * @ORM\ManyToOne(targetEntity="Network\StoreBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="identity", type="string", length=255, nullable=true)
     */
    protected $identity;

    /**
     * @var array
     *
     * @ORM\Column(name="attributes", type="array", length=255, nullable=true)
     */
    protected $attributes;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->attributes = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentity($identity)
    {
        if ($this->identity) {
            throw new \LogicException('The identity was set before. It is not allowed update it');
        }

        $this->identity = $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getIdentity();
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
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

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
}


