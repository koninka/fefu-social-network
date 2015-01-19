<?php

namespace Network\StoreBundle\Service;

use Doctrine\ORM\EntityManager;
use Network\StoreBundle\DBAL\RoleCommunityEnumType;
use Network\StoreBundle\Entity\UserCommunity;

class CommunityService
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
    
    private function getFindBy($id, $class) {
        return $this->em->getRepository('NetworkStoreBundle:'.$class)->find($id);
    }

    public function persist($community)
    {
        $this->em->persist($community);
        $this->em->flush();
    }
    
    public function remove($userCom)
    {
        $this->em->remove($userCom);
        $this->em->flush();
    }

    public function getUserCommunityById($user, $communityId)
    {
        return $this->em->getRepository('NetworkStoreBundle:UserCommunity')->findOneBy([
                'user' => $user,
                'community' => $communityId
            ]);
    }
    
    public function getUserCommunityInviteeById($user, $communityId)
    {
        return $this->em->getRepository('NetworkStoreBundle:UserCommunity')->findOneBy([
                'user' => $user,
                'community' => $communityId,
                'role' => RoleCommunityEnumType::RC_INVITEE 
            ]);
    }
    
    public function getUserCommunityAskingById($user, $communityId)
    {
        return $this->em->getRepository('NetworkStoreBundle:UserCommunity')->findOneBy([
                'user' => $user,
                'community' => $communityId,
                'role' => RoleCommunityEnumType::RC_ASKING
            ]);
    }
    
    public function getUserAskingById($communityId)
    {
        return $this->em->getRepository('NetworkStoreBundle:UserCommunity')->findBy([
                'community' => $communityId,
                'role' => RoleCommunityEnumType::RC_ASKING 
            ]);
    }
    
    public function getUserInviteeById($user)
    {
        return $this->em->getRepository('NetworkStoreBundle:UserCommunity')->findBy([
                'user' => $user,
                'role' => RoleCommunityEnumType::RC_INVITEE 
            ]);
    }
    
    public function getFindByUserId($id)
    {
        return $this->getFindBy($id, 'User');
    }
    
    public function getFindByCommunityId($id)
    {
        return $this->getFindBy($id, 'Community');
    }
     
    public function getFindByUserCommunityId($id)
    {
        return $this->em->getRepository('NetworkStoreBundle:UserCommunity')->findBy([
                'community' => $id
            ]);
    }
    
    public function getSearchCommunity($search)
    {
        return $query = $this->em->createQuery(
                'SELECT p FROM NetworkStoreBundle:Community p WHERE p.name LIKE :search')
                    ->setParameter('search', $search)->getResult();
    }
    
    public function CreateCommunity($community, $user) {
        $community->setOwner($user);
        $userCom = new UserCommunity();
        $userCom->setUser($user)
            ->setCommunity($community)
            ->SetRole(RoleCommunityEnumType::RC_OWNER);
        $this->persist($community);
        $this->persist($userCom);
        
        return $community;
    }
    
    public function UpdateCommunity($community, $isClose) {
        if ($isClose && $community->getType() === TypeCommunityEnumType::C_OPEN) {
            $asking = $this->getUserAskingById($community->getId());
            foreach ($asking as $val) {
                $val->setRole(RoleCommunityEnumType::RC_PARTICIPANT);
                $this->persist($val);
            }
        }
        $this->persist($community);
        
        return $community;
    }
    
    public function ShowCommunity($id, $user) {
        $rels = $this->em->getRepository('NetworkStoreBundle:Relationship');
        $parti =  $this->getFindByUserCommunityId($id);
        $ans_friends = [];
        $friends_invitee = [];
        $asking = [];
        $participants = [];
        if ($user) {
            $friends = $rels->findFriendsForUser($user->getId());
            foreach ($friends as $val) {
                $involved = true;
                foreach ($parti as $value) {
                    $involved = $involved && ($val->getPartner() != $value->getUser());
                    if ($value->getRole() === RoleCommunityEnumType::RC_INVITEE) {
                        array_push($friends_invitee, $value);
                    }
                }
                if ($involved && !($val->getPartner() === $user)) {
                    array_push($ans_friends, $val->getPartner());
                }
            }
        }
        foreach ($parti as $value) {
            if ($value->getRole() === RoleCommunityEnumType::RC_ASKING) {
                array_push($asking, $value->getUser());
            } 
            if ($value->getRole() == RoleCommunityEnumType::RC_PARTICIPANT 
                    || $value->getRole() == RoleCommunityEnumType::RC_OWNER) {
                array_push($participants, $value->getUser());
            } 
        }
        
        return [
                 $friends_invitee, $ans_friends, $participants, $asking 
            ];
    }
}
