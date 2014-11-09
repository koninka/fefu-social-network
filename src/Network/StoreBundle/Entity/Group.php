<?php

namespace Network\StoreBundle\Entity;

use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_group")
 */

class Group extends BaseGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var array
     */
    protected $roles;

    public function __construct()
    {
        $this->name = '';
        $this->roles = [];
    }

    public function __toString()
    {
        return (string) $this->name;
    }

}
