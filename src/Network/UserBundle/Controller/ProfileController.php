<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Network\UserBundle\Controller;

use FOS\UserBundle\Controller\ProfileController as BaseController;
use FOS\UserBundle\Model\UserInterface;
use Network\UserBundle\Form\Type\ContactInfoType;
use Network\UserBundle\Form\Type\CommunityType;
use Network\UserBundle\Form\Type\CreateCommunityType;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Network\StoreBundle\Entity\Community;
use Network\StoreBundle\Entity\UserCommunity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Network\StoreBundle\Entity\Thread;
use Network\StoreBundle\Entity\Post;
use Network\StoreBundle\DBAL\RoleCommunityEnumType;
use Network\StoreBundle\DBAL\TypeCommunityEnumType;


class ProfileController extends BaseController
{
    use ProfileTrait;

    public function showAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->redirect($this->generateUrl('user_profile', ['id' => $user->getId()]));
    }

    public function profileAction($id, Request $request)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        $rels = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship');

        $isCurUser = false;
        $fsStatus = RelationshipStatusEnumType::FS_NONE;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $curUser = $this->getUser();
            $isCurUser = ($curUser->getId() === $user->getId());
            $fsStatus = $rels->getRelationshipForUser($curUser->getId(), $user->getId())->getStatus();
        }

        return $this->render('NetworkUserBundle:Profile:show.html.twig', [
            'user' => $user,
            'rl_status' => $fsStatus,
            'is_cur_user' => $isCurUser
        ]);
    }

    public function showFriendsAction($id)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        $rels = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship');

        $isCurUser = false;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $curUser = $this->getUser();
            $isCurUser = ($curUser->getId() === $user->getId());
        }

        return $this->render('NetworkUserBundle:Profile:friends.html.twig', [
            'is_cur_user' => $isCurUser,
            'user_id' => $user->getId(),
            'friends' => $rels->findFriendsForUser($user->getId()),
            'subscribers' => $rels->findSubscribersForUser($user->getId()),
            'subscribed_on' => $rels->findSubscribedOnForUser($user->getId())
        ]);
    }

    public function showProfileFriendsAction()
    {
        $user = $this->getUser();
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        return $this->showFriendsAction($user->getId());
    }

    public function manageFriendshipRequestsAction()
    {
        $rels = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship');

        return $this->render('NetworkUserBundle:Profile:manage_requests.html.twig', [
            'friendship_requests' => $rels->findFriendshipRequestsForUser($this->getUser()->getId())
        ]);
    }

    public function contactAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $formContact = $this->container->get('form.factory')->create(
            new ContactInfoType(),
            $user->getContactInfo()
        );
        $formContact->handleRequest($request);

        if ($formContact->isValid()) {
            $em = $this->container->get('doctrine')->getManager();
            $em->persist($user->getContactInfo());
            $em->flush();

            return $this->redirect( $this->generateUrl('user_profile', ['id' => $user->getId()]));
        }

        return $this->render('FOSUserBundle:Profile:contact.html.twig',[
            'form' =>  $formContact->createView()
        ]);
    }


    public function showIMAction()
    {
        $user = $this->getUser();
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        return $this->render('NetworkUserBundle:Profile:im.html.twig', [
            'user_id' => $user->getId()
        ]);
    }

    public function postAction(Request $request)
    {
        $imService = $this->get('network.store.im_service');
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        if (!array_key_exists('text', $data) or trim($data['text']) == '') {
            return new JsonResponse(['error' => 'field "text" is empty']);
        }
        if (array_key_exists('threadId', $data)) {
            $threadRepo = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');
            if ($threadRepo->checkPermission($data['threadId'], $user->getId())) {
                throw new AccessDeniedException('This user does not have access to this section.');
            }
        } else {
            $recipientUser = $this->getDoctrine()
                                  ->getRepository('NetworkStoreBundle:User')
                                  ->find($data['recipientId']);

            if (!$recipientUser) {
                // TODO: handle error case when there's no recipient user found by id
            }


            // TODO: decide what to do when posting to yourself
            // currently it does Internal Server Error (500)
            $thread = $this->getDoctrine()
                           ->getRepository('NetworkStoreBundle:Thread')
                           ->findByUsers($user->getId(), $recipientUser->getId());

            if (!$thread or count($thread) == 0) {
                // there's no 1x1 thread between this pair of users
                // so we're creating a new one
                $thread = new Thread();
                $thread->setTopic('no topic');
                $imService->persistThread($thread); //because of foreign key error
                $thread->addUser($user)
                       ->addUser($recipientUser);

                $imService->persistThread($thread);

            } elseif (count($thread) > 1) {
                // TODO: handle exceptional error case when there's somehow more
                // than one 1x1 thread for this pair of users
            } else {
                $thread = $thread[0];
            }
        }

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set("UTC");

        $post = new Post();
        $post->setText($data['text'])
             ->setTs(new \DateTime('now'))
             ->setUser($user)
             ->setThread($thread);

        $imService->persistPost($post);

        date_default_timezone_set($oldTimeZone);

        return new JsonResponse(['threadId' => $thread->getId()]);
    }

    public function threadListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $threadRepo = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');

        // TODO: sort threads by date; implement in repo
        $threads = $threadRepo->getThreadListForUser($user->getId());

        $response = new JsonResponse();
        $response->setData($threads);

        return $response;
    }

    public function threadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if ($data == null || !array_key_exists('id', $data)) {
            return new JsonResponse(['error' => 'field "id" is empty']);
        }
        $threadId = $data['id'];
        $threadRepo = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');
        if (!$threadRepo->checkPermission($threadId, $user->getId())) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $posts = $this->getDoctrine()
                      ->getRepository('NetworkStoreBundle:Post')
                      ->getThreadPosts($threadId);
        return new JsonResponse($posts);
    }
        }


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
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        $communities = $user->getCommunities();
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
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
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
