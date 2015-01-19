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
        $msg = $this->get('network.store.community_service')->excludeUser($user, $id, 'go_out');
       
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function deleteCommunityAction($id)
    {
        $user = $this->getUser();
        $msg = $this->get('network.store.community_service')->deleteCommunity($user, $id);
                
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function joinCommunityAction($id)
    {
        $user = $this->getUser();
        $msg = $this->get('network.store.community_service')->joinCommunity($user, $id);
               
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function inviteCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $msg = $this->get('network.store.community_service')->inviteCommunity($user, $id, $communityId);
        
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function excludeCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $msg = $this->get('network.store.community_service')->excludeCommunity($user, $id, $communityId);
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function acceptRequestCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $msg = $this->get('network.store.community_service')->acceptRequestCommunity($user, $id, $communityId);
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function rejectRequestCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $msg = $this->get('network.store.community_service')->deleteUserCommunity($user, $id, $communityId, 'reject_request');

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function uninviteCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $msg = $this->get('network.store.community_service')->deleteUserCommunity($user, $id, $communityId, 'uninvite');
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }
    
    public function cancelRequestCommunityAction($id, $communityId)
    {
        $user = $this->getUser();
        $msg = $this->get('network.store.community_service')->deleteUserCommunity($user, $id, $communityId, 'cancel_request');
        
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
            $community = $communityService->getSearchCommunity($search);
        }
       
        return $this->render('NetworkWebBundle:Search:search_community.html.twig', [
            'user' => $user,
            'search' => $search,
            'community' => $community
        ]);
    }
    
}

