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
use Network\StoreBundle\Entity\ContactInfo;
use Network\UserBundle\Form\Type\ContactInfoType;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Network\StoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Network\StoreBundle\Entity\Thread;
use Network\StoreBundle\Entity\Post;

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
            $fsStatus = $curUser->getRelationshipStatus($id);
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

        $isCurUser = false;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $curUser = $this->getUser();
            $isCurUser = ($curUser->getId() === $user->getId());
        }

        return $this->render('NetworkUserBundle:Profile:im.html.twig', [
            'user_id' => $user->getId()
        ]);
    }

    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $recipientUser = $this->getDoctrine()
                              ->getRepository('NetworkStoreBundle:User')
                              ->find($data['id']);

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
            $thread->setTopic('default topic')
                   ->addUser($user)
                   ->addUser($recipientUser);
            $em->persist($thread);
            $em->flush();

        } elseif (count($thread) > 1) {
            // TODO: handle exceptional error case when there's somehow more
            // than one 1x1 thread for this pair of users
        } else {
            $thread = $thread[0];
        }

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set("UTC");

        // TODO: check for $data['text'] existence and size
        $post = new Post();
        $post->setText($data['text'])
             ->setTs(new \DateTime('now'))
             ->setUser($user)
             ->setThread($thread);

        $em->persist($post);
        $em->flush();

        date_default_timezone_set($oldTimeZone);

        $r = ['threadId' => $thread->getId()];

        $response = new JsonResponse();
        $response->setData($r);
        return $response;
    }

    public function threadListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $threads = $this->getDoctrine()
               ->getRepository('NetworkStoreBundle:Thread')
               ->getThreadListForUser($user->getId());

        $r = [];


        foreach ($threads as $t) {
            $r[] = [
                'id' => $t->getId(),
                'topic' => $t->getTopic(),
            ];
        }

        $response = new JsonResponse();
        $response->setData($r);
        return $response;
    }

    public function threadAction(Request $request)
    {
        // TODO: check if user is a member of requested thread
        // user shouldn't be able to see it if he's not a member
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        $posts = $this->getDoctrine()
                      ->getRepository('NetworkStoreBundle:Post')
                      ->getThreadPosts($data['id']);

        $r = [];

        foreach ($posts as $p) {
            $r[] = [
                'id' => $p->getId(),
                'ts' => $p->getTs(),
                'text' => $p->getText(),
                'from' => $p->getUser()->getFirstName() . " " . $p->getUser()->getLastName()
            ];
        }

        $response = new JsonResponse();
        $response->setData($r);
        return $response;
    }
}

