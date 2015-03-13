<?php

namespace Network\UserBundle\Controller;

use FOS\UserBundle\Controller\ProfileController as BaseController;
use FOS\UserBundle\Model\UserInterface;
use Network\UserBundle\Form\Type\ContactInfoType;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FeedController extends Controller
{
    use ProfileTrait;

    public function showFeedAction()
    {
        $user = $this->getUser();
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        $feed = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');

        return $this->render('NetworkUserBundle:Feed:showFeed.html.twig', [
            'feed' => $feed->getFeedForUser($user->getId()),
        ]);
    }

    public function showThreadPageAction($id)
    {
        $threadRep = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');
        if(!$threadRep->isThreadFromWall($id)) return $this->redirect($this->generateUrl('mainpage'));

        $user = $threadRep->getUserByWallThreadId($id);
        $fromUser = $user != null;
        $community = null;
        if(!$fromUser){
            $community = $threadRep->getCommunityByWallThreadId($id);
        }

        $params = [
            'thread' => $threadRep->getThreadData($id),
            'fromUser' => $fromUser,
            'threadId' => $id,
            ];

        if($fromUser){
            $params['userId'] = $user->getId();
            $params['firstName'] = $user->getFirstName();
            $params['lastName'] = $user->getLastName();
        } else {
            $params['commId'] = $community->getId();
            $params['commName'] = $community->getName();
        }

        return $this->render('NetworkUserBundle:Feed:showThread.html.twig', $params);
    }

    public function showUserFeedFromFriendsAction()
    {
        $user = $this->getUser();
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        $feed = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');

        return $this->render('NetworkUserBundle:Feed:showFeed.html.twig', [
            'feed' => $feed->getFriendsFeed($user->getId()),
        ]);
    }

    public function showUserFeedFromCommunitiesAction()
    {
        $user = $this->getUser();
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        $feed = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');

        return $this->render('NetworkUserBundle:Feed:showFeed.html.twig', [
            'feed' => $feed->getCommunitiesFeed($user->getId()),
        ]);
    }

    public function addThreadToBlacklistAction($id)
    {
        $user = $this->getUser();
        $thread = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread')->find($id);
        if (empty($user) || empty($thread))
            return $this->redirect($this->generateUrl('mainpage'));

        $user->getBlacklist()->addThread($thread);
        $userManager = $this->get('fos_user.user_manager');
        $userManager->updateUser($user);

        return $this->render('NetworkUserBundle:Feed:threadDeleted.html.twig', [
            'threadId' => $id,
        ]);
    }

    public function removeThreadFromBlacklistAction($id)
    {
        $user = $this->getUser();
        $thread = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread')->find($id);
        if (empty($user) || empty($thread))
            return $this->redirect($this->generateUrl('mainpage'));

        $user->getBlacklist()->removeThread($thread);
        $userManager = $this->get('fos_user.user_manager');
        $userManager->updateUser($user);

        return $this->showFeedAction();
    }
}
