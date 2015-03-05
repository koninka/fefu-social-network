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

        return $this->showUserFeedAction($user->getId());
    }

    public function showUserFeedAction($id)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        $feed = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');

        $isCurUser = false;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $curUser = $this->getUser();
            $isCurUser = ($curUser->getId() === $user->getId());
        }

        return $this->render('NetworkUserBundle:Feed:showFeed.html.twig', [
            'user_id' => $user->getId(),
            'is_cur_user' => $isCurUser,
            'feed' => $feed->getFeedForUser($user->getId()),
        ]);
    }

}
