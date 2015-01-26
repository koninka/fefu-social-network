<?php

namespace Network\StoreBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Exception\Exception;
use Network\StoreBundle\Entity\Thread;
use Network\StoreBundle\Entity\Post;
use Network\StoreBundle\Entity\User;
use Network\StoreBundle\DBAL\ThreadEnumType;
use Symfony\Component\HttpFoundation\JsonResponse;

class PollService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }
    
    public function getPollInfo($answer)
    {
        $users = [];
        foreach ($answer->getUser() as $user){
            $users[] = ['id' => $user->getId(), 'text' => $user->getFirstName() . ' ' . $user->getLastName()];
        }
        
        return ['id' => $answer->getId(), 'users' => $users];
    }
}