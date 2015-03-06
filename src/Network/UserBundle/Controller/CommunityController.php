<?php

namespace Network\UserBundle\Controller;

use Network\StoreBundle\DBAL\TypeCommunityEnumType;
use Network\StoreBundle\DBAL\RoleCommunityEnumType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StoreBundle\Entity\Community;
use Symfony\Component\HttpFoundation\Request;
use Network\UserBundle\Form\Type\CommunityType;
use Network\UserBundle\Form\Type\CreateCommunityType;


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
    
    public function showProfileCommunityAction(Request $request)
    {
        $user = $this->getUser();
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        return $this->communityAction($user->getId(), $request);
    }
    
    public function communityAction($id, Request $request)
    {
        $curUser = $this->getUser();
        $communityService = $this->get('network.store.community_service');
        $user = $communityService->getFindByUserId($id);
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));
        $isCurUser = $curUser === $user;
        $community = new Community();
        $form = $this->container->get('form.factory')->create(
            new CreateCommunityType(),
            $community 
        );
        $hasForm = false; 
        if ($isCurUser && $request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $community = $communityService->createCommunity($community, $user);
    
                return $this->redirect( $this->generateUrl('user_edit_community', ['id' => $community->getId()]));
            }
            $hasForm = true;
        }

        //$communities = $user->getCommunities()->toArray();
        $communities =  $this->getDoctrine()->getEntityManager()
            ->getRepository('NetworkStoreBundle:Community')
            ->getCommunitiesForUser($user->getId());

        return $this->render('NetworkUserBundle:Profile:community.html.twig', [
            'user' => $user,
            'communities' => $communities,
            'form' => $form->createView(),
            'is_error_form' => $hasForm,
            'is_cur_user' => $isCurUser
        ]);
    }
    
    public function editCommunityAction($id, Request $request)
    {
        $user = $this->getUser();
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));
        $communityService = $this->get('network.store.community_service');
        $community = $communityService->getFindByCommunityId($id);
        if ($community->getOwner()->getId() !== $user->getId()) {
            return showCommunityAction($id, $request);
        }
        $form = $this->container->get('form.factory')->create(
            new CommunityType(),
            $community 
        );
        $isClose = $community->getType() === TypeCommunityEnumType::C_CLOSED;
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $community = $communityService->updateCommunity($community, $isClose);
                
                return $this->redirect( $this->generateUrl('user_show_community', ['id' => $community->getId()]));
            }
        }
        
        return $this->render('NetworkUserBundle:Profile:edit_community.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
    
    public function showCommunityAction($id)
    {
        $user = $this->getUser();
        $communityService = $this->get('network.store.community_service');
        $community = $communityService->getFindByCommunityId($id);
        if (empty($community)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }
        $isRole = false;
        $isOwner = false;
        if ($user) {
            $isOwner = $this->getDoctrine()
                ->getRepository('NetworkStoreBundle:Community')
                ->userInCommunityRole($user->getId(), 
                    $community->getId(), RoleCommunityEnumType::RC_OWNER);
            $rel = $this->getDoctrine()
                ->getRepository('NetworkStoreBundle:Community')
                ->getUser($user->getId(), $community->getId());
            if ($rel) {
                $isRole = $rel->getRole();
            }
        }
        list ($friends_invitee, $ans_friends, $participants, $asking) 
                = $communityService->showCommunity($id, $user);
        
        return $this->render('NetworkUserBundle:Profile:show_community.html.twig', [
            'user' => $user,
            'community' => $community,
            'is_role' => $isRole,
            'is_owner' => $isOwner,
            'friends' => $ans_friends,
            'friends_invitee' => $friends_invitee,
            'asking' => $asking,
            'participants' => $participants
        ]);
    }
    
}

