<?php

namespace Network\StoreBundle\Service;

use Doctrine\ORM\EntityManager;

class ImService
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

    public function createThread($thread)
    {
        $this->em->persist($thread);
        $this->em->flush();
    }

    public function createPost($post)
    {
        $this->em->persist($post);
        $this->em->flush();
    }

    public function getThreadById($threadId)
    {
        return $this->em->getRepository('NetworkStoreBundle:Thread')->findOneById($threadId);
    }

}
