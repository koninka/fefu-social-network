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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Network\StoreBundle\Entity\User;
use Network\StoreBundle\Entity\Friendship;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends BaseController
{
    /**
     * Show the user
     */
    public function showAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->redirect($this->generateUrl('user_profile', ['id' => $user->getId()]));

//        return $this->render('FOSUserBundle:Profile:show.html.twig', array(
//            'user' => $user
//        ));
    }

    public function profileAction($id, Request $request)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        $is_friend = false;
        $is_cur_user = false;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $cur_user = $this->getUser();
            $is_cur_user = ($cur_user->getId() === $user->getId());
            $is_friend = ($cur_user->hasFriend($id));
        }

        return $this->render('NetworkUserBundle:Profile:show.html.twig', [
            'user' => $user,
            'is_friend' => $is_friend,
            'is_cur_user' => $is_cur_user
        ]);
    }

    public function addFriendAction($id, Request $request)
    {
        $user_friend = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user_friend)) {
            return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'Не найден пользователь'
            ]);
        }
        $user = $this->getUser();

        if ($user->hasFriend($id)) {
            return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'Пользователь уже в друзьях'
            ]);
        }

        $friend = new Friendship();
        $friend->setUser($user);
        $friend->setFriend($user_friend);
        $user->addFriend($friend);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => 'Заявка отправлена'
        ]);

    }

    public function deleteFriendAction($id, Request $request)
    {
        $user_friend = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user_friend)) {
            return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'Не найден пользователь'
            ]);
        }
        $user = $this->getUser();

        if (!$user->hasFriend($id)) {
            return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'Пользователь не является вашим другом'
            ]);
        }

        $friend = $this->getDoctrine()->getRepository('NetworkStoreBundle:UserFriend')->findOneBy(['user' => $user->getId(), 'friend' => $user_friend->getId()]);

        $em = $this->getDoctrine()->getManager();
        $em->remove($friend);
        $em->flush();

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => 'Пользователь удален из друзей'
        ]);

    }

}
