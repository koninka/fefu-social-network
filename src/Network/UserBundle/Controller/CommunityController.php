<?php

namespace Network\UserBundle\Controller;

use Network\StoreBundle\DBAL\TypeCommunityEnumType;
use Network\StoreBundle\DBAL\RoleCommunityEnumType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StoreBundle\Entity\UserCommunity;
use Network\StoreBundle\Entity\Community;
use Symfony\Component\HttpFoundation\Request;
use Network\UserBundle\Form\Type\CommunityType;


class CommunityController extends Controller
{
    use ProfileTrait;
    
    public function goOutCommunityAction($id)
    {
        $user = $this->getUser();
        $communityService = $this->get('network.store.community_service');
        $userCom =  $communityService->getUserCommunityById($user, $id);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif (!$userCom) {
            $msg = 'msg.user_not_member_in_community';
        } else {
            $communityService->remove($userCom);
            $msg = 'msg.go_out_community';             
        }
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function deleteCommunityAction($id)
    {
        $user = $this->getUser();
        $communityService = $this->get('network.store.community_service');
        $community =  $communityService->getFindByCommunityId($id);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif (!$community) {
            $msg = 'msg.not_this_community';
        } elseif ($community->getOwner() !== $user) { 
            $msg = 'msg.user_does_not_have_the_right_to_delete_the_community';
        } else {
            $userCom = $communityService->getFindByUserCommunityId($id);
            foreach ($userCom as $com) {
                $communityService->remove($com);
            }
            $communityService->remove($community);
            $msg = 'msg.delete_community';             
        }
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function joinCommunityAction($id)
    {
        $user = $this->getUser();
        $communityService = $this->get('network.store.community_service');
        $userCom =  $communityService->getUserCommunityById($user, $id);
        $community =  $communityService->getFindByCommunityId($id);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif ($userCom) {
            if ($userCom->getRole() == RoleCommunityEnumType::RC_INVITEE) {
                $userCom->setRole(RoleCommunityEnumType::RC_PARTICIPANT);
                $msg = 'msg.user_joined_into_community';
            } else {
                $msg = 'msg.user_in_community';
            }
        } else {
            $userCom = new UserCommunity();
            $userCom->setUser($user)->setCommunity($community);
            if ($community->getOwner() === $user) {
                $userCom->setRole(RoleCommunityEnumType::RC_OWNER); 
                $msg = 'msg.user_joined_into_community';
            } else if ($community->getType() === TypeCommunityEnumType::C_OPEN) {
                $userCom->setRole(RoleCommunityEnumType::RC_PARTICIPANT);
                $msg = 'msg.user_joined_into_community';
            } else {
               $userCom->setRole(RoleCommunityEnumType::RC_ASKING); 
               $msg = 'msg.user_application_is_considered';
            }
            $communityService->persist($userCom);
        }
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function inviteCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $communityService = $this->get('network.store.community_service');
        $userFriend = $communityService->getFindByUserId($id);
        $userCom =  $communityService->getUserCommunityById($userFriend, $communityId);
        $community =  $communityService->getFindByCommunityId($communityId); 
        $owner = $communityService->getUserCommunityById($user, $communityId);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif ($userCom) {
            $msg = 'msg.user_in_community';
        } elseif (!$owner || !($owner->getRole() === RoleCommunityEnumType::RC_OWNER)) {   
            $msg = 'msg.user_does_not_have_the_right_to_invite_the_community';
        } else {
            $userCom = new UserCommunity();
            $userCom->setUser($userFriend)->setCommunity($community);
            $userCom->setRole(RoleCommunityEnumType::RC_INVITEE); 
            $communityService->persist($userCom);
            $msg = 'msg.invitation_sent';
        }
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function excludeCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $communityService = $this->get('network.store.community_service');
        $userFriend = $communityService->getFindByUserId($id);
        $userCom =  $communityService->getUserCommunityById($userFriend, $communityId);
        $owner = $communityService->getUserCommunityById($user, $communityId);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif (!$userCom) {
            $msg = 'msg.user_not_in_community';
        } elseif (!$owner || !($owner->getRole() === RoleCommunityEnumType::RC_OWNER)) {   
            $msg = 'msg.user_does_not_have_the_right_to_invite_the_community';
        } else {
            $communityService->remove($userCom);
            $msg = 'msg.exclude_community';
        }
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function acceptRequestCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $communityService = $this->get('network.store.community_service');
        $userFriend = $communityService->getFindByUserId($id);
        $userCom =  $communityService->getUserCommunityById($userFriend, $communityId);
        $owner = $communityService->getUserCommunityById($user, $communityId);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif (!$userCom) {
            $msg = 'msg.user_did_not_send_the_request';
        } elseif ((!$owner || !($owner->getRole() === RoleCommunityEnumType::RC_OWNER))
                && !($user->getId() == $id)) {   
            $msg = 'msg.user_does_not_have_the_right_to_invite_the_community';
        } else {
            $userCom->setRole(RoleCommunityEnumType::RC_PARTICIPANT);
            $communityService->persist($userCom);
            $msg = 'msg.joined_into_community';
        }
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function rejectRequestCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $communityService = $this->get('network.store.community_service');
        $userFriend = $communityService->getFindByUserId($id);
        $userCom =  $communityService->getUserCommunityById($userFriend, $communityId);
        $owner = $communityService->getUserCommunityById($user, $communityId);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif (!$userCom) {
            $msg = 'msg.user_did_not_send_the_request';
        } elseif ((!$owner || !($owner->getRole() === RoleCommunityEnumType::RC_OWNER))
                && !($user->getId() == $id)) {   
            $msg = 'msg.user_does_not_have_the_right_to_invite_the_community';
        } else {
            $communityService->remove($userCom);
            $msg = 'msg.reject_request';
        } 
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function uninviteCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $communityService = $this->get('network.store.community_service');
        $userFriend = $communityService->getFindByUserId($id);
        $userCom =  $communityService->getUserCommunityInviteeById($userFriend, $communityId);
        $owner = $communityService->getUserCommunityById($user, $communityId);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif (!$userCom) {
            $msg = 'msg.user_did_not_send_the_request';
        } elseif ((!$owner || $owner->getRole() !== RoleCommunityEnumType::RC_OWNER)
                && $user->getId() != $id) {   
            $msg = 'msg.user_does_not_have_the_right_to_uninvite_the_community';
        } else {
            $communityService->remove($userCom);
            $msg = 'msg.uninvite_community';
        } 
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function cancelRequestCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $communityService = $this->get('network.store.community_service');
        $userFriend = $communityService->getFindByUserId($id);
        $userCom =  $communityService->getUserCommunityAskingById($userFriend, $communityId);
        $owner = $communityService->getUserCommunityById($user, $communityId);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif (!$userCom) {
            $msg = 'msg.user_did_not_send_the_request';
        } elseif ((!$owner || $owner->getRole() !== RoleCommunityEnumType::RC_OWNER)
                && $user->getId() != $id) {   
            $msg = 'msg.user_does_not_have_the_right_to_invite_the_community';
        } else {
            $communityService->remove($userCom);
            $msg = 'msg.cancel_request';
        } 
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function searchCommunityAction(Request $request)
    {
        $user = $this->getUser();
        $search = $request->get('search');
        $community = $search;
        if ($search) {
            $communityService = $this->get('network.store.community_service');
            $community = $communityService->getSearchCommunity($search.'%');
        }
       
        return $this->render('NetworkWebBundle:Search:search_community.html.twig', [
            'user' => $user,
            'search' => $search,
            'community' => $community
        ]);
    }
    
}

