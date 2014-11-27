<?php

namespace Network\UserBundle\Controller;

use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Network\StoreBundle\Entity\Relationship;

class FriendController extends Controller
{

    public function sendFriendshipRequestAction($id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $userFriend = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        $msg = 'Не найден пользователь';
        if (!empty($userFriend)) {
            $status = $user->getRelationshipStatus($id);
            if ($status === RelationshipStatusEnumType::FS_ACCEPTED) {
                $msg = 'Пользователь уже в друзьях';
            } elseif ($status === RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
                $msg = 'Вы уже подписаны на пользователя';
            } elseif ($status === RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER) {
                $friendRelationship = $userFriend->getRelationship($user->getId());
                $friendRelationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                $em->persist($friendRelationship);

                $relationship = $user->getRelationship($id);
                $relationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                $em->persist($relationship);

                $em->flush();
                $msg = 'Заявка на дружбу принята';
            } elseif ($status != RelationshipStatusEnumType::FS_NONE) {
                $msg = $status.'Неизвестная ошибка';
            } else {
                //$status != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER
                $relationship = new Relationship();
                $relationship->setUser($user)->setPartner($userFriend)->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
                $em->persist($relationship);

                $em->flush();

                $friendRelationship = new Relationship();
                $friendRelationship->setUser($userFriend)->setPartner($user)->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER);
                $em->persist($friendRelationship);

                $em->flush();

                $msg = 'Заявка отправлена';
            }
        }
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);

    }

    public function acceptFriendshipRequestAction($id)
    {
        $user = $this->getUser();
        $userFriend = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        $msg = 'Не найден пользователь';
        if (!empty($userFriend)) {
            $status = $userFriend->getRelationshipStatus($user->getId());
            if ($status === RelationshipStatusEnumType::FS_ACCEPTED) {
                $msg = 'Пользователь уже в друзьях';
            } elseif ($status != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
                $msg = 'Пользователь не отправлял вам запрос на дружбу (не подписывался на вас)';
            } else {
                $em = $this->getDoctrine()->getManager();

                $friendRelationship = $userFriend->getRelationship($user->getId());
                $friendRelationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                $em->persist($friendRelationship);

                $relationship = $user->getRelationship($id);
                $relationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                $em->persist($relationship);

                $em->flush();
                $msg = 'Заявка на дружбу принята';
            }
        }
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }

    public function declineFriendshipAction($id)
    {
        $user = $this->getUser();
        $userFriend = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        $msg = 'Не найден пользователь';
        if (!empty($userFriend)) {
            $status = $userFriend->getRelationshipStatus($user->getId());
            if ($status === RelationshipStatusEnumType::FS_ACCEPTED) {
                $msg = 'Пользователь уже в друзьях';
            } elseif ($status != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
                $msg = 'Пользователь не отправлял вам запрос на дружбу (не подписывался на вас)';
            } else {
                $em = $this->getDoctrine()->getManager();

                $friendRelationship = $userFriend->getRelationship($user->getId());
                $friendRelationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
                $em->persist($friendRelationship);

                $relationship = $user->getRelationship($id);
                $relationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER);
                $em->persist($relationship);

                $em->flush();

                $msg = 'Заявка отклонена';
            }
        }

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }

    public function deleteFriendshipAction($id)
    {
        $user = $this->getUser();
        $userFriend = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        $msg = 'Не найден пользователь';
        if (!empty($userFriend)) {
            $status = $userFriend->getRelationshipStatus($user->getId());
            if ($status != RelationshipStatusEnumType::FS_ACCEPTED) {
                $msg = 'Пользователь не является вашим другом';
            } else {
                $em = $this->getDoctrine()->getManager();

                $friendRelationship = $userFriend->getRelationship($user->getId());
                $friendRelationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
                $em->persist($friendRelationship);

                $relationship = $user->getRelationship($id);
                $relationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER);
                $em->persist($friendRelationship);

                $em->flush();

                $msg = 'Пользователь удален из друзей';
            }
        }
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);

    }

}