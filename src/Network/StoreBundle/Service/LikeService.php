<?php

namespace Network\StoreBundle\Service;

use Doctrine\ORM\EntityManager;

class LikeService
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
    
    public function like($user, $data) 
    {
        if (empty($data) || !array_key_exists('id', $data)) {
            throw new Exception('field \'id\' is empty');
        }
        $entity = $this->em->getRepository($data['class'] == 'post' 
                ? 'NetworkStoreBundle:Post' 
                : 'ApplicationSonataMediaBundle:Media')
                ->find($data['id']);
        if (empty($entity)) {
            throw new AccessDeniedException('This record is not a post or photo can not be like.');
        }

        $method = sprintf('%sLike', $entity->getLikes()->contains($user) ? 'remove' : 'add');
        $entity->$method($user);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return ['count' => $entity->getLikes()->count(), 'id' => $entity->getId(), 'status' => 'ok'];
    }   
    
    public function countLike($data) 
    {
        if (empty($data) || !array_key_exists('id', $data)) {
            throw new Exception('field \'id\' is empty');
        }
        $entity = $this->em->getRepository($data['class'] == 'post' 
                ? 'NetworkStoreBundle:Post' 
                : 'ApplicationSonataMediaBundle:Media')
                ->find($data['id']);
        if (empty($entity)) {
            throw new AccessDeniedException('This record is not a post or photo can not be like.');
        }

        return ['count' => $entity->getLikes()->count(), 'id' => $entity->getId(), 'status' => 'ok'];
    } 
    
    public function userLike($data) 
    {
        if (empty($data) || !array_key_exists('id', $data)) {
            throw new Exception('field \'id\' is empty');
        }
        $entity = $this->em->getRepository($data['class'] == 'post' 
                ? 'NetworkStoreBundle:Post' 
                : 'ApplicationSonataMediaBundle:Media')
                ->find($data['id']);
        if (empty($entity)) {
            throw new AccessDeniedException('This record is not a post or photo can not be like.');
        }
        $users = [];
        foreach ($entity->getLikes() as $user){
            $users[] = ['id' => $user->getId(), 'text' => $user->getFirstName() . ' ' . $user->getLastName()];
        }

        return ['users'=>$users, 'id' => $entity->getId(), 'status' => 'ok'];
    }

}
