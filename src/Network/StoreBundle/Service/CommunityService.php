<?php

namespace Network\StoreBundle\Service;

use Doctrine\ORM\EntityManager;
use Network\StoreBundle\DBAL\RoleCommunityEnumType;
use Network\StoreBundle\Entity\UserCommunity;
use Network\StoreBundle\DBAL\TypeCommunityEnumType;


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

    public function getUserAskingById($communityId)
    {
        return $this->em->getRepository('NetworkStoreBundle:Community')
                ->getUserRole($communityId, RoleCommunityEnumType::RC_ASKING);
    }
    
    public function getUserInviteeById($user)
    {
        return $this->em->getRepository('NetworkStoreBundle:Community')
                ->getUserRole($user->getId(), RoleCommunityEnumType::RC_INVITEE);
    }
    
    public function getFindByUserId($id)
    {
        return $this->em->getRepository('NetworkStoreBundle:Community')
                ->getFindBy($id, 'User');
    }
    
    public function getFindByCommunityId($id)
    {
        return $this->em->getRepository('NetworkStoreBundle:Community')
                ->getFindBy($id, 'Community');
    }
    
    function excludeUser($user, $communityId, $class) 
    {
        $community = $this->getFindByCommunityId($communityId);
        if (empty($user)) return 'msg.not_authorized';
        if ($this->em->getRepository('NetworkStoreBundle:Community')
                ->isUserInCommunity($user->getId(), $community->getId())) return 'msg.user_not_member_in_community';
        $this->em->getRepository('NetworkStoreBundle:Community')
                ->excludeUser($user->getId(), $community->getId());
        return 'msg.'.$class.'_community';
    }
    
    function deleteCommunity($user, $communityId) 
    {
        $community = $this->getFindByCommunityId($communityId);
        if (empty($user)) return 'msg.not_authorized';
        if (empty($community)) return 'msg.not_this_community';
        if ($community->getOwner() !== $user) { 
            return 'msg.user_does_not_have_the_right_to_delete_the_community';
        }
        $this->em->getRepository('NetworkStoreBundle:Community')
                ->excludeUsers($community->getId());
        $this->remove($community);
        $msg = 'msg.delete_community';  
        return $msg;
    }
    
    function joinCommunity($user, $communityId) 
    {
        $community = $this->getFindByCommunityId($communityId);
        $repository = $this->em->getRepository('NetworkStoreBundle:Community');
        if (empty($user)) return 'msg.not_authorized';
        if (empty($community)) return 'msg.not_this_community';
        if (!($repository->isUserInCommunity($user->getId(), $community->getId()))) {
            $rel = $repository->UserInCommunityRole($user->getId(), 
                        $community->getId(), RoleCommunityEnumType::RC_INVITEE);
            if (!empty($rel)) {
                $rel[0]->setRole(RoleCommunityEnumType::RC_PARTICIPANT);
                $this->persist($rel[0]);
                $msg = 'msg.user_joined_into_community';
            } else {
                $msg = 'msg.user_in_community';
            }
        } else {
            $rel = new UserCommunity();
            $rel->setUser($user)->setCommunity($community);
            if ($community->getOwner() === $user) {
                $rel->setRole(RoleCommunityEnumType::RC_OWNER); 
                $msg = 'msg.user_joined_into_community';
            } else if ($community->getType() === TypeCommunityEnumType::C_OPEN) {
                $rel->setRole(RoleCommunityEnumType::RC_PARTICIPANT);
                $msg = 'msg.user_joined_into_community';
            } else {
               $rel->setRole(RoleCommunityEnumType::RC_ASKING); 
               $msg = 'msg.user_application_is_considered';
            }
            $this->persist($rel);
        }
        
        return $msg;
    }
    
    function inviteCommunity($user, $id, $communityId) 
    {
        $community = $this->getFindByCommunityId($communityId);
        $repository = $this->em->getRepository('NetworkStoreBundle:Community');
        if (empty($user)) return 'msg.not_authorized';
        if (empty($community)) return 'msg.not_this_community';
        $userFriend = $this->getFindByUserId($id);
        $msg = 'msg.user_not_found';
        if (!$this->em->getRepository('NetworkStoreBundle:Community')
                ->isUserInCommunity($userFriend->getId(), $community->getId())) {
            $msg = 'msg.user_in_community';
        } elseif (empty($repository->UserInCommunityRole($user->getId(), 
                        $community->getId(), RoleCommunityEnumType::RC_OWNER))) {   
            $msg = 'msg.user_does_not_have_the_right_to_invite_the_community';
        } else {
            $rel = new UserCommunity();
            $rel->setUser($userFriend)->setCommunity($community);
            $rel->setRole(RoleCommunityEnumType::RC_INVITEE); 
            $this->persist($rel);
            $msg = 'msg.invitation_sent';
        }
        
        return $msg;
    }
    
    function excludeCommunity($user, $id, $communityId) 
    {
        $community = $this->getFindByCommunityId($communityId);
        if (empty($user)) return 'msg.not_authorized';
        if ($community->getOwner()->getId() !== $user->getId())return 'msg.user_does_not_have_the_right_to_invite_the_community';
        $userFriend = $this->getFindByUserId($id);
         
        return $this->excludeUser($userFriend, $communityId, 'go_out');
    }
    
    function  acceptRequestCommunity ($user, $id, $communityId)
    {
        $community = $this->getFindByCommunityId($communityId);
        $userFriend = $this->getFindByUserId($id);
        if (empty($user)) 
            return 'msg.not_authorized';
        if (($community->getOwner() !== $user) && ($userFriend->getId() !== $user->getId()))
            return 'msg.user_does_not_have_the_right_to_invite_the_community';
        if (($community->getOwner() === $user) && ($userFriend->getId() !== $user->getId()) ) {
            $rel = $this->em->getRepository('NetworkStoreBundle:Community')
                    ->UserInCommunityRole($userFriend->getId(), 
                      $community->getId(), RoleCommunityEnumType::RC_ASKING);
            if ($rel) {
                $rel[0]->setRole(RoleCommunityEnumType::RC_INVITEE);
                $this->persist($rel[0]);
            }
        }
        
        return $this->joinCommunity($userFriend, $communityId);
    }
    
    function  deleteUserCommunity ($user, $id, $communityId, $class) 
    {
        $community = $this->getFindByCommunityId($communityId);
        $userFriend = $this->getFindByUserId($id);
        if (empty($user)) 
            return 'msg.not_authorized';
        if (($community->getOwner() !== $user) && ($userFriend->getId() !== $user->getId()))
            return 'msg.user_does_not_have_the_right_to_invite_the_community';

        return $this->excludeUser($userFriend, $communityId, $class);
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
        $parti =  $this->em->getRepository('NetworkStoreBundle:Community')->getUsers($id);
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
    
    public function getSearchCommunity($search)
    {
        return $query = $this->em->createQuery(
         'SELECT p FROM NetworkStoreBundle:Community p WHERE p.name LIKE :search')
             ->setParameter('search', $search.'%')->getResult();
    }
}
