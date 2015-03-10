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
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

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

        $userRefInfo = [
            'friends' => 182,
            'photos'  => 723,
            'videos'  => 145,
            'audios'  => 301,
            'groups'  => 45,
        ];

        return $this->render('NetworkUserBundle:Profile:show.html.twig', [
            'user' => $user,
            'rl_status' => $fsStatus,
            'is_cur_user' => $isCurUser,
            'user_ref_info' => $userRefInfo,
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

            return $this->redirect($this->generateUrl('user_profile', ['id' => $user->getId()]));
        }

        return $this->render('FOSUserBundle:Profile:contact.html.twig', [
            'form' =>  $formContact->createView()
        ]);
    }

    public function showProfileAlbumsAction()
    {
        $user = $this->getUser();
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        return $this->showAlbumsAction($user->getId());
    }

    public function showAlbumsAction($id)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        $albums = $this->getDoctrine()->getRepository('NetworkStoreBundle:UserGallery');

        $isCurUser = false;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $curUser = $this->getUser();
            $isCurUser = ($curUser->getId() === $user->getId());
        }

        return $this->render('NetworkUserBundle:Albums:albums.html.twig', [
            'user_id' => $user->getId(),
            'is_cur_user' => $isCurUser,
            'albums' => $albums->findAlbumsForUser($user->getId()),
        ]);
    }

    public function getProfileRequestsAction() {

        $threads = $this->getDoctrine()->getRepository('NetworkStoreBundle:UserThread');
        $threadsUnread = $threads->getThreadsUnreadIdForUser($this->getUser()->getId());

        $threadsId = [];
        foreach($threadsUnread as $thread) {
            $threadsId[] = intval($thread['thread_id']);
        }

        $result = [
            'threadsId' => $threadsId
        ];

        return new JsonResponse($result);
    }

}
